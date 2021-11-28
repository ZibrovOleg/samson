<?require_once($_SERVER['DOCUMENT_ROOT']. "/bitrix/modules/main/include/prolog_before.php");?>

<?php
use Bitrix\Main\Context;
use Bitrix\Iblock\ElementTable;
use Bitrix\Main\Entity\ReferenceField;

$request = Context::getCurrent()->getRequest();
$xmlId = $request->getPost("xml_id");

if (!check_bitrix_sessid() && empty($xmlId))
	return 1;

$iblockId = 3;

$rsElement = ElementTable::getList([
	'filter' => [
		"IBLOCK_ID" => $iblockId, 
		"XML_ID" => $xmlId,
		[
			"LOGIC" => "OR",
			["=PROPERTY_CODE" => "SIZES_SHOES"],
			["=PROPERTY_CODE" => "SIZES_CLOTHES"]
		]
	],
	'select' => [
		"ID", "NAME", "CODE", "IBLOCK_ID", 
		"XML_ID", "IBLOCK_SECTION_ID", 
		"DETAIL_PAGE_URL" => "IBLOCK.DETAIL_PAGE_URL",

		'PROPERTY_CODE' => 'PROPERTY_PROP.CODE', 
		'PROPERTY_VALUE' => 'PROPERTY.VALUE',
		'PROPERTY_ENUM_VALUE' => 'PROPERTY_ENUM.VALUE', 
		'PROPERTY_ENUM_XML_ID' => 'PROPERTY_ENUM.XML_ID', 
		'PROPERTY_ENUM_ID' => 'PROPERTY_ENUM.ID',
		
		'PRODUCT_ID' => 'PRODUCTS.ID', 
		'PRICES_PRICE' => 'PRICES.PRICE', 
	],
	'runtime' => [
		new Bitrix\Main\Entity\ReferenceField(
			'PROPERTY',
			'\Mogera\Iblock\ElementProperyTable',
			['=this.ID' => 'ref.IBLOCK_ELEMENT_ID'],
			['join_type' => 'LEFT']
		),
		new Bitrix\Main\Entity\ReferenceField(
			'PROPERTY_PROP',
			'\Bitrix\Iblock\PropertyTable',
			['=this.PROPERTY.IBLOCK_PROPERTY_ID' => 'ref.ID'],
			['join_type' => 'LEFT']
		),
		new Bitrix\Main\Entity\ReferenceField(
			'PROPERTY_ENUM',
			'\Bitrix\Iblock\PropertyEnumerationTable',
			['=this.PROPERTY.IBLOCK_PROPERTY_ID' => 'ref.PROPERTY_ID'],
			['join_type' => 'LEFT']
		),

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

$arData = [];
while ($arElement = $rsElement->fetch()) 
{
	if ($arElement["PROPERTY_VALUE"] === $arElement["PROPERTY_ENUM_ID"])
	{
		$detailPageUrl = \CIBlock::ReplaceDetailUrl(
			$arElement["DETAIL_PAGE_URL"], $arElement, false, "E"
		);

		$arData = [
			"XML_ID" => $arElement["XML_ID"],
			"NAME" => $arElement["NAME"],
			'DETAIL_PAGE_URL' => $detailPageUrl,
			"SIZES" => $arElement["PROPERTY_ENUM_VALUE"],
			"PRICE" => number_format($arElement["PRICES_PRICE"], 0, '', ''),
		];
	}
}

if (!$arData) return 1;
?>
<td class="first-field" scope="row">
	<input type="text" name="xml_id[]" value="<?=$arData['XML_ID']?>">
</td>
<td><a href="<?=$arData['DETAIL_PAGE_URL']?>"><?=$arData['NAME']?></a></td>
<td><?=$arData['SIZES']?></td>
<td><?=$arData['PRICE']?></td>
<td>
	<button type="button" class="btn btn-secondary btn-sm remove-goods">удалить</button>
</td>
