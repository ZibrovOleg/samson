<?require_once($_SERVER['DOCUMENT_ROOT']. "/bitrix/modules/main/include/prolog_before.php");?>

<?php
use Bitrix\Catalog\PriceTable;
use Bitrix\Catalog\ProductTable;
use Bitrix\Currency\CurrencyManager;
use Bitrix\Iblock\ElementTable;
use Bitrix\Main\Application;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Sale;

$context = Application::getInstance();
$request = $context->getContext()->getRequest();

$siteId = $context->getContext()->getSite();
$xmlId = $request->getPost("xml_id");

if (!check_bitrix_sessid() && empty($xmlId))
	return;

$iblockId = 3;
$quantity = 1;

$rsElement = ElementTable::getList([
	'filter' => [
		"IBLOCK_ID" => $iblockId, 
		"XML_ID" => $xmlId
	],
	'select' => [
		"ID", "NAME", "CODE", "IBLOCK_ID", "IBLOCK_SECTION_ID", 
		"DETAIL_PAGE_URL" => "IBLOCK.DETAIL_PAGE_URL",

		'PRODUCT_ID' => 'PRODUCTS.ID', 
		'PRODUCT_QUANTITY' => 'PRODUCTS.QUANTITY', 
		'PRODUCT_MEASURE' => 'PRODUCTS.MEASURE',

		'PRODUCT_WIDTH' => 'PRODUCTS.WIDTH',
		'PRODUCT_HEIGHT' => 'PRODUCTS.HEIGHT',
		'PRODUCT_LENGTH' => 'PRODUCTS.LENGTH',

		'PRODUCT_PRICE_ID' => 'PRICES.ID',
		'PRICE_TYPE_ID' => 'PRICES.CATALOG_GROUP_ID',
		'PRICES_PRICE' => 'PRICES.PRICE', 
		'PRICES_CURRENCY' => 'PRICES.CURRENCY',
	],
	'runtime' => [
		new ReferenceField(
			'PRODUCTS',
			'\Bitrix\Catalog\ProductTable',
			['=this.ID' => 'ref.ID'],
			['join_type' => 'LEFT']
		),
		new ReferenceField(
			'PRICES',
			'\Bitrix\Catalog\PriceTable',
			['=this.ID' => 'ref.PRODUCT_ID'],
		),
	],
]);

$basket = Sale\Basket::loadItemsForFUser(
	Sale\Fuser::getId(), 
	$siteId
);

while ($arElement = $rsElement->fetch()) {
	$productId = $arElement["ID"];

	if ($item = $basket->getExistsItem('catalog', $productId)) 
	{
		$item->setField('QUANTITY', $item->getQuantity() + $quantity);
	}
	else 
	{
		$arMeasure = ProductTable::getCurrentRatioWithMeasure($productId);   

		$notes = '';
		$priceBase = 0;
		$arPrice = PriceTable::getList([
			'filter'=> [
				'CATALOG_GROUP.XML_ID' => 'BASE', 
				'PRODUCT_ID' => $productId,
			],
			'select' => [
				'PRICE', 'CATALOG_GROUP_ID',
				'NAME' => 'GROUP_LANG.NAME' 
			],
			'runtime' => [
				new ReferenceField(
					'GROUP_LANG',
					'\Bitrix\Catalog\GroupLangTable',
					['=this.CATALOG_GROUP_ID' => 'ref.CATALOG_GROUP_ID'],
				),
			]
		])->fetchAll();
		foreach ($arPrice as $key => $price) 
		{
			$notes = $price["NAME"]; 
			$priceBase = $price["PRICE"];
		}

		$dimensions = serialize([
			'WIDTH' => $arElement["PRODUCT_WIDTH"],
			'HEIGHT' => $arElement["PRODUCT_HEIGHT"],
			'LENGTH' => $arElement["PRODUCT_LENGTH"],
		]);

		$item = $basket->createItem('catalog', $productId);
		$item->setFields([
			'QUANTITY' => $quantity,
			'CURRENCY' => CurrencyManager::getBaseCurrency(),
			'PRODUCT_PRICE_ID' => $arElement["PRODUCT_PRICE_ID"],
			'PRICE_TYPE_ID' => $arElement["PRICE_TYPE_ID"],
			'PRICE' => $arElement["PRICES_PRICE"],
			'BASE_PRICE' => $priceBase,
			'LID' => $siteId,
			'PRODUCT_PROVIDER_CLASS' => '\Bitrix\Catalog\Product\CCatalogProductProvider',
			'NOTES' => $notes,
			'DETAIL_PAGE_URL' => \CIBlock::ReplaceDetailUrl($arElement["DETAIL_PAGE_URL"], $arElement, false, "E"),
			'DIMENSIONS' => $dimensions,
			'MEASURE_CODE' => $arMeasure[$productId]["MEASURE"]["CODE"],
			'MEASURE_NAME' => $arMeasure[$productId]["MEASURE"]["SYMBOL_RUS"],
		]);
	}
	$basket->save();
}
