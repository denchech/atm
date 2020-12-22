<?php

namespace App\Controller;

use App\Command\CashCommand;
use App\Command\WithdrawTransactionCommand;
use App\Command\TransferTransactionCommand;
use App\DBAL\Types\OperationType;
use App\Entity\Card;
use App\Entity\Transaction;
use App\Error\CashError;
use App\Form\CashFormType;
use App\Form\WithdrawTransactionFormType;
use App\Form\TransferTransactionFormType;
use App\Service\Atm;
use App\Service\BankSystem;
use App\Service\TransactionHandler;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class AtmController extends AbstractController
{
    private TransactionHandler $transactionHandler;

    private BankSystem $bankSystem;

    private TranslatorInterface $translator;

    private Atm $atm;

    public function __construct(
        TransactionHandler $transactionHandler,
        BankSystem $bankSystem,
        Atm $atm,
        TranslatorInterface $translator
    ) {
        $this->transactionHandler = $transactionHandler;
        $this->bankSystem         = $bankSystem;
        $this->translator         = $translator;
        $this->atm                = $atm;
    }

    /**
     * @Route("/", name="index")
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function index(): Response
    {
        return $this->render(
            'atm/index.html.twig',
            [
                'controller_name' => 'ATMController',
            ]
        );
    }

    /**
     * @Route("/recharge", name="recharge")
     * @IsGranted("ROLE_RECHARGE")
     */
    public function recharge(Request $request): Response
    {
        $command = new CashCommand();

        $form = $this->createForm(CashFormType::class, $command);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$this->atm->cashByCurrencyAndValueExists($command->getCurrency(), $command->getValue())) {
                $message = $this->translator->trans(CashError::VALUE_DOES_NOT_EXIST);
                $form->get('value')->addError(new FormError($message));

                return $this->render(
                    'atm/recharge.html.twig',
                    [
                        'form' => $form->createView(),
                    ]
                );
            }

            $value = $command->getValue() * $command->getCount();

            $transaction = $this->createTransaction(
                OperationType::RECHARGE,
                (string) $value,
                $command->getCurrency()
            );

            $this->transactionHandler->process($transaction);

            if ($transaction->isCancelled()) {
                $this->addErrorToForm($form, $transaction);

                return $this->render(
                    'atm/withdraw.html.twig',
                    [
                        'form' => $form->createView(),
                    ]
                );
            }

            return $this->success($transaction);
        }

        return $this->render(
            'atm/recharge.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/withdraw", name="withdraw")
     * @IsGranted("ROLE_WITHDRAWAL")
     */
    public function withdraw(Request $request): Response
    {
        $command = new WithdrawTransactionCommand();
        $form    = $this->createForm(WithdrawTransactionFormType::class, $command);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $preparedCash = $this->atm->prepareCash($command->getValue(), $command->getCurrency());

            if (empty($preparedCash)) {
                $message = $this->translator->trans(CashError::NO_CASH_FOR_VALUE);
                $path    = 'value';

                $form->get($path)->addError(new FormError($message));

                return $this->render(
                    'atm/withdraw.html.twig',
                    [
                        'form' => $form->createView(),
                    ]
                );
            }

            $transaction = $this->createTransaction(
                OperationType::WITHDRAWAL,
                (string) $command->getValue(),
                $command->getCurrency()
            );

            $this->transactionHandler->process($transaction);

            if ($transaction->isCancelled()) {
                $this->addErrorToForm($form, $transaction);

                return $this->render(
                    'atm/withdraw.html.twig',
                    [
                        'form' => $form->createView(),
                    ]
                );
            }

            $this->atm->removeCash($preparedCash);

            return $this->success($transaction);
        }

        return $this->render(
            'atm/withdraw.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/transfer", name="transfer")
     * @IsGranted("ROLE_TRANSFER")
     */
    public function transfer(Request $request): Response
    {
        $command = new TransferTransactionCommand();
        $form    = $this->createForm(TransferTransactionFormType::class, $command);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $secondCard = $this->bankSystem->findCard($command->getSecondCard());

            $transaction = $this->createTransaction(
                OperationType::TRANSFER,
                $command->getValue(),
                $command->getCurrency()
            );
            $transaction->setSecondCard($secondCard);

            $this->transactionHandler->process($transaction);

            if ($transaction->isCancelled()) {
                $this->addErrorToForm($form, $transaction);

                return $this->render(
                    'atm/transfer.html.twig',
                    [
                        'form' => $form->createView(),
                    ]
                );
            }

            return $this->success($transaction);
        }

        return $this->render(
            'atm/transfer.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    private function success(Transaction $transaction): Response
    {
        return $this->render(
            'atm/success.html.twig',
            [
                'transactionID' => $transaction->getId(),
                'operation'     => $transaction->getOperation(),
                'currency'      => $transaction->getCurrency(),
            ]
        );
    }

    private function createTransaction(string $operation, string $value, string $currency): Transaction
    {
        /** @var Card $firstCard */
        $firstCard = $this->getUser();

        $transaction = new Transaction();
        $transaction->setOperation($operation);
        $transaction->setFirstCard($firstCard);
        $transaction->setValue($value);
        $transaction->setCurrency($currency);

        return $transaction;
    }

    private function addErrorToForm(FormInterface $form, Transaction $transaction): void
    {
        $message = $this->translator->trans($transaction->getError()->getMessage());
        $path    = $transaction->getError()->getPath();

        $form->get($path)->addError(new FormError($message));
    }
}
