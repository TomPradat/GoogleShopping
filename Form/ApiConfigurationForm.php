<?php


namespace GoogleShopping\Form;

use GoogleShopping\GoogleShopping;
use Thelia\Core\Translation\Translator;
use Thelia\Form\BaseForm;
use Symfony\Component\Validator\Constraints;

class ApiConfigurationForm extends BaseForm
{
    protected function buildForm()
    {
        $this->formBuilder
            ->add("client_id", "text", array(
                'required' => true,
                'label' => Translator::getInstance()->trans(
                    'googleshopping.configuration.clientid',
                    array(),
                    GoogleShopping::DOMAIN_NAME
                ),
                'data' => GoogleShopping::getConfigValue('client_id'),
                'label_attr' => array(
                    'for' => 'client_id'
                ),
                "constraints" => array(
                    new Constraints\NotBlank(),
                )
            ))
            ->add("client_secret", "text", array(
                'required' => true,
                'label' => Translator::getInstance()->trans(
                    'googleshopping.configuration.clientsecret',
                    array(),
                    GoogleShopping::DOMAIN_NAME
                ),
                'data' => GoogleShopping::getConfigValue('client_secret'),
                'label_attr' => array(
                    'for' => 'client_secret'
                ),
                "constraints" => array(
                    new Constraints\NotBlank(),
                )
            ))
            ->add("application_name", "text", array(
                'required' => true,
                'label' => Translator::getInstance()->trans(
                    'googleshopping.configuration.applicationname',
                    array(),
                    GoogleShopping::DOMAIN_NAME
                ),
                'data' => GoogleShopping::getConfigValue('application_name'),
                'label_attr' => array(
                    'for' => 'application_name'
                ),
                "constraints" => array(
                    new Constraints\NotBlank(),
                )
            ));
    }
    
    public function getName()
    {
        return "googleshopping_api_configuration";
    }

}
