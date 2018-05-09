<?

Class MorizoDigital{

   public static function Init(){

       AddEventHandler("sale", "OnBeforeSaleComponentOrderOneStepSaveOrder", array("MorizoDigital", "OnBeforeSaleComponentOrderOneStepSaveOrderHandler"));

       AddEventHandler("catalog", "OnSuccessCatalogImport1C", array("MorizoDigital", "OnSuccessCatalogImport1CHandler"));

       AddEventHandler("iblock", "OnBeforeIBlockSectionUpdate", array("MorizoDigital", "OnBeforeIBlockSectionUpdateHandler"));

       self::setCookie();

   }

    /*
     *
     * Задача № 1
     * Сохраняем utm_source в куки
     *
     */
    public static function setCookie(){

        $application = Bitrix\Main\Application::getInstance();
        $context = $application->getContext();
        $request = $context->getRequest();

        $utm_source = $request->get('utm_source');
        $cookieValue = $request->getCookie('UTM_SOURCE');

        if(strlen($utm_source) && $utm_source !== $cookieValue){

            $cookie = new Bitrix\Main\Web\Cookie("UTM_SOURCE", $utm_source, time() + 60*60*24*60);
            $cookie->setDomain($context->getServer()->getHttpHost());
            $cookie->setHttpOnly(false);

            $context->getResponse()->addCookie($cookie);
            $context->getResponse()->flush("");


        }

    }

    /*
    *
    * Задача № 1
    * Обработчик сохранения свайства заказа
    *
    */
   public static function OnBeforeSaleComponentOrderOneStepSaveOrderHandler(Order $order, &$arUserResult, $request, &$arParams, &$arResult){


       $request = Bitrix\Main\Application::getInstance()->getContext()->getRequest();

       $cookieValue = $request->getCookie('UTM_SOURCE');

       if(!strlen($cookieValue))
           return;

       $propertyCollection = $order->getPropertyCollection();

       foreach ($propertyCollection as $index=>$property)
       {
           $arProperty = $property->getProperty();

           if($arProperty["CODE"]=="UTM_SOURCE"){

               $property->setValue($cookieValue);;
           }


       }

   }

   /*
    * Задача № 2.1
    *
    */
   public static function OnSuccessCatalogImport1CHandler(){

       $arMailFields = Array(
          "EMAIL_FROM" => COption::GetOptionString("main","email_from", "nobody@nobody.com"),
          "EMAIL_TO" => ""
       );

       CEvent::Send('SUCCESS_CATALOG_IMPORT', SITE_ID, $arMailFields);


   }

   /*
    * Задача № 2.2
    *
    */
    public static function OnBeforeIBlockSectionUpdateHandler(&$arFields)
    {

        //Check source
        if(!isset($_SESSION["BX_CML2_IMPORT"]["NS"]))
            return;

        \Bitrix\Main\Loader::includeModule("iblock");

        $arSect = CIBlockSection::GetByID($arFields["ID"])->GetNext();

        if( ($arFields["CODE"] !== $arSect["CODE"]) || ($arFields["NAME"] !== $arSect["NAME"])){

            global $APPLICATION;
            $APPLICATION->throwException("Действие запрещено для обмена данными с 1С");
            return false;
        }
    }
}

MorizoDigital::Init();
?>