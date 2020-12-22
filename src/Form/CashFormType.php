<?php

namespace App\Form;

use App\Command\CashCommand;
use App\DBAL\Types\CurrencyType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CashFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'currency',
                ChoiceType::class,
                [
                    'choices'    => CurrencyType::getChoices(),
                    'empty_data' => CurrencyType::RUBLES,
                ]
            )
            ->add('value', NumberType::class)
            ->add('count', IntegerType::class)
            ->add('submit', SubmitType::class, ['label' => 'Add'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => CashCommand::class,
            ]
        );
    }
}
