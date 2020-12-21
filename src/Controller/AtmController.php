<?php

namespace App\Controller;

use App\Command\TransactionCommand;
use App\Command\TransferTransactionCommand;
use App\DBAL\Types\OperationType;
use App\Entity\Card;
use App\Entity\Transaction;
use App\Form\TransactionFormType;
use App\Form\TransferTransactionFormType;
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

    public function __construct(
        TransactionHandler $transactionHandler,
        BankSystem $bankSystem,
        TranslatorInterface $translator
    ) {
        $this->transactionHandler = $transactionHandler;
        $this->bankSystem         = $bankSystem;
        $this->translator         = $translator;
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
        $command = new TransactionCommand();
        $form    = $this->createForm(TransactionFormType::class, $command, ['submit' => 'recharge']);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $transaction = $this->createTransaction(OperationType::RECHARGE, (string) $command->getValue());

            $this->transactionHandler->process($transaction);

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
        $command = new TransactionCommand();
        $form    = $this->createForm(TransactionFormType::class, $command, ['submit' => 'withdraw']);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $transaction = $this->createTransaction(OperationType::WITHDRAWAL, (string) $command->getValue());

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

            $transaction = $this->createTransaction(OperationType::TRANSFER, $command->getValue());
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
            ]
        );
    }

    private function createTransaction(string $operation, string $value): Transaction
    {
        /** @var Card $firstCard */
        $firstCard = $this->getUser();

        $transaction = new Transaction();
        $transaction->setOperation($operation);
        $transaction->setFirstCard($firstCard);
        $transaction->setValue($value);

        return $transaction;
    }

    private function addErrorToForm(FormInterface $form, Transaction $transaction): void
    {
        $message = $this->translator->trans($transaction->getError()->getMessage());
        $path    = $transaction->getError()->getPath();

        $form->get($path)->addError(new FormError($message));
    }
}
