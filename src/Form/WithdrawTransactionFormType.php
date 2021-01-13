<?php

namespace App\Form;

use App\Command\WithdrawTransactionCommand;
use App\DBAL\Types\CurrencyType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WithdrawTransactionFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('value', IntegerType::class)
            ->add(
                'currency',
                ChoiceType::class,
                [
                    'choices'    => CurrencyType::getChoices(),
                    'empty_data' => CurrencyType::RUBLES,
                ]
            )
            ->add('withdraw', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => WithdrawTransactionCommand::class,
            ]
        );
    }
}
