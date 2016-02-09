<?php

namespace GoogleShopping\Controller\Admin;

use GoogleShopping\Event\GoogleShoppingEvents;
use GoogleShopping\Form\ApiConfigurationForm;
use GoogleShopping\Form\MerchantConfigurationForm;
use GoogleShopping\GoogleShopping;
use GoogleShopping\Handler\GoogleShoppingHandler;
use GoogleShopping\Model\GoogleshoppingAccount;
use GoogleShopping\Model\GoogleshoppingAccountQuery;
use GoogleShopping\Model\GoogleshoppingConfiguration;
use GoogleShopping\Model\GoogleshoppingConfigurationQuery;
use Thelia\Controller\Admin\BaseAdminController;
use Thelia\Core\HttpFoundation\JsonResponse;
use Thelia\Core\Security\AccessManager;
use Thelia\Core\Security\Resource\AdminResources;
use Thelia\Model\ProductCategoryQuery;

class ConfigurationController extends BaseAdminController
{
    public function viewAllAction($params = array())
    {
        if (null !== $response = $this->checkAuth(array(AdminResources::MODULE), 'GoogleShopping', AccessManager::VIEW)) {
            return $response;
        }

        $notEmptyCategory = ProductCategoryQuery::create()
            ->filterByDefaultCategory(1)
            ->select('category_id')
            ->groupBy('category_id')
            ->find()
            ->toArray();

        return $this->render(
            "google-shopping/configuration",
            array(
                "sync_secret" => GoogleShopping::getConfigValue('sync_secret'),
                "not_empty_category" => implode(',', $notEmptyCategory)
            )
        );
    }

    public function saveApiConfiguration()
    {
        if (null !== $response = $this->checkAuth(array(AdminResources::MODULE), array('GoogleShopping'), AccessManager::CREATE)) {
            return $response;
        }

        $message = null;

        $form = new ApiConfigurationForm($this->getRequest());

        try {
            $formData = $this->validateForm($form)->getData();

            foreach ($formData as $name => $value) {
                if ($name === "success_url" || $name === "error_message") {
                    continue;
                }
                GoogleShopping::setConfigValue($name, $value);
            }

            return $this->generateRedirect('/admin/module/GoogleShopping');

        } catch (\Exception $e) {
            $message = $e->getMessage();
        }

        $this->setupFormErrorContext(
            $this->getTranslator()->trans("GoogleShopping configuration", [], GoogleShopping::DOMAIN_NAME),
            $message,
            $form,
            $e
        );


        return $this->render('module-configure', array('module_code' => 'GoogleShopping'));
    }

    public function saveMiscConfiguration()
    {
        if (null !== $response = $this->checkAuth(array(AdminResources::MODULE), array('GoogleShopping'), AccessManager::CREATE)) {
            return $response;
        }

        $message = null;

        $form = $this->createForm("googleshopping.misc.configuration");

        try {
            $data = $this->validateForm($form, 'POST')->getData();

            GoogleShopping::setConfigValue('check_gtin', boolval($data['check_gtin']));
            GoogleShopping::setConfigValue('attribute_color', implode(',',$data['attribute_color']));
            GoogleShopping::setConfigValue('attribute_size', implode(',',$data['attribute_size']));

            return $this->generateRedirect('/admin/module/GoogleShopping');

        } catch (\Exception $e) {
            $message = $e->getMessage();
        }

        $this->setupFormErrorContext(
            $this->getTranslator()->trans("GoogleShopping configuration", [], GoogleShopping::DOMAIN_NAME),
            $message,
            $form,
            $e
        );

        return $this->render('module-configure', array('module_code' => 'GoogleShopping'));
    }

    public function addMerchantAccount()
    {
        if (null !== $response = $this->checkAuth(array(AdminResources::MODULE), array('GoogleShopping'), AccessManager::CREATE)) {
            return $response;
        }

        $form = $this->createForm("googleshopping.merchant.configuration");

        try {
            $data = $this->validateForm($form, 'POST')->getData();

            $googleShoppingConfiguration = new GoogleshoppingConfiguration();

            $googleShoppingConfiguration->setTitle($data['title'])
                ->setMerchantId($data['merchant_id'])
                ->setLangId($data['lang_id'])
                ->setCountryId($data['country_id'])
                ->setCurrencyId($data['currency_id']);

            $isDefault = boolval($data['is_default']);
            $synchronisation = boolval($data['sync']);

            if (true === $isDefault) {
                $defaultAccounts = GoogleshoppingConfigurationQuery::create()
                    ->filterByIsDefault(true)
                    ->find();
                /** @var GoogleshoppingConfiguration $defaultAccount */
                foreach ($defaultAccounts as $defaultAccount) {
                    $defaultAccount->setIsDefault(false)
                        ->save();
                }
            }

            $googleShoppingConfiguration->setIsDefault($isDefault)
                ->setSync($synchronisation)
                ->save();
            return new JsonResponse(["message" => "Configuration added with success !"], 200);

        } catch (\Exception $e) {
            return new JsonResponse($e->getMessage(), 500);
        }
    }

    public function updateMerchantAccount($id)
    {
        if (null !== $response = $this->checkAuth(array(AdminResources::MODULE), array('GoogleShopping'), AccessManager::CREATE)) {
            return $response;
        }

        $form = $this->createForm("googleshopping.merchant.configuration");

        try {
            $data = $this->validateForm($form, 'POST')->getData();

            $googleShoppingConfiguration = GoogleshoppingConfigurationQuery::create()
                ->findOneById($id);

            $isDefault = boolval($data['is_default']);
            $synchronisation = boolval($data['sync']);

            if (true === $isDefault) {
                $defaultAccounts = GoogleshoppingConfigurationQuery::create()
                    ->filterByIsDefault(true)
                    ->find();
                /** @var GoogleshoppingConfiguration $defaultAccount */
                foreach ($defaultAccounts as $defaultAccount) {
                    $defaultAccount->setIsDefault(false)
                        ->save();
                }
            }

            if (null !== $googleShoppingConfiguration) {
                $googleShoppingConfiguration->setTitle($data['title'])
                    ->setMerchantId($data['merchant_id'])
                    ->setLangId($data['lang_id'])
                    ->setCountryId($data['country_id'])
                    ->setCurrencyId($data['currency_id'])
                    ->setIsDefault($isDefault)
                    ->setSync($synchronisation)
                    ->save();
            }

            return new JsonResponse(["message" => "Configuration updated with success !"], 200);

        } catch (\Exception $e) {
            return new JsonResponse($e->getMessage(), 500);
        }
    }

    public function deleteMerchantAccount($id)
    {
        if (null !== $response = $this->checkAuth(array(AdminResources::MODULE), array('GoogleShopping'), AccessManager::CREATE)) {
            return $response;
        }

        $form = $this->createForm("googleshopping.merchant.configuration");

        try {
            $data = $this->validateForm($form, 'POST')->getData();

            $googleShoppingConfiguration = GoogleshoppingConfigurationQuery::create()
                ->findOneById($id);

            if (null !== $googleShoppingConfiguration) {
                $googleShoppingConfiguration->delete();
            }

            return new JsonResponse(["message" => "Configuration deleted with success !"], 200);

        } catch (\Exception $e) {
            return new JsonResponse($e->getMessage(), 500);
        }
    }
}
