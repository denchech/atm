<?php

namespace App\Controller;

use App\Command\CashCommand;
use App\Entity\Cash;
use App\Form\CashFormType;
use App\Service\Atm;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class EmployeeController extends AbstractController
{
    private Atm $atm;

    public function __construct(Atm $atm)
    {
        $this->atm = $atm;
    }

    /**
     * @Route("/employee/login", name="app_login_employee")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('employee_cash');
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render(
            'security/employee_login.html.twig',
            ['last_username' => $lastUsername, 'error' => $error]
        );
    }

    /**
     * @Route("/employee/cash", name="employee_cash")
     *
     * @IsGranted("ROLE_EMPLOYEE")
     */
    public function cash(Request $request): Response
    {
        $command = new CashCommand();

        $form = $this->createForm(CashFormType::class, $command);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $cash = $this->atm->findCashByCurrencyAndValue($command->getCurrency(), $command->getValue());

            if (null === $cash) {
                $cash = new Cash();
            }

            $cash->setCurrency($command->getCurrency());
            $cash->setValue($command->getValue());
            $newCount = $cash->getCount() + $command->getCount();
            $cash->setCount($newCount);

            $this->atm->saveCash($cash);
        }

        $allCash = $this->atm->findAllCashSorted();

        return $this->render(
            'employee/cash.html.twig',
            [
                'form'    => $form->createView(),
                'allCash' => $allCash,
            ]
        );
    }
}
