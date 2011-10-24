<?php

namespace Stfalcon\Bundle\PaymentBundle\Form\Payments\Interkassa;

use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\AbstractType;

class PayType extends AbstractType
{
    
    public function buildForm(FormBuilder $builder, array $options)
    {
        //@todo для полей формы нужно отключить добавление к имени названия формы
        // "stfalcon_payments_interkassa_pay_ik_shop_id" => "ik_shop_id"

        $builder
            ->add('ik_shop_id', 'hidden')
            ->add('ik_payment_amount', 'hidden')
            ->add('ik_payment_id', 'hidden')
            ->add('ik_payment_desc', 'hidden')
            ->add('ik_paysystem_alias', 'hidden')
            ->add('ik_baggage_fields', 'hidden')
            ->add('ik_sign_hash', 'hidden')
            ->add('amount', 'text', array('read_only' => true, 'label' => 'Сумма к оплате'));
    }
    
    public function getName()
    {
        return 'stfalcon_payments_interkassa_pay';
    }

}