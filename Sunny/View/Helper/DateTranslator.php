<?php

// TODO А ты его проверял? Ничего что метод должен называться как последняя часть класса

/**
 * Returns date in current language
 * @author Maximka
 *
 */
class Sunny_View_Helper_DateTranslator extends Zend_View_Helper_Abstract
{
	private $_month = array(
		'01' => array(
			'uk' => 'січня',
			'ru' => 'января',
			'en' => 'january'
		),
		'02' => array(
			'uk' => 'лютого',
			'ru' => 'февраля',
			'en' => 'february'
		),
		'03' => array(
			'uk' => 'березня',
			'ru' => 'марта',
			'en' => 'march'
		),
		'04' => array(
			'uk' => 'квітня',
			'ru' => 'апреля',
			'en' => 'april'
		),
		'05' => array(
			'uk' => 'травня',
			'ru' => 'мая',
			'en' => 'may'
		),
		'06' => array(
			'uk' => 'червня',
			'ru' => 'июня',
			'en' => 'june'
		),
		'07' => array(
			'uk' => 'липня',
			'ru' => 'июля',
			'en' => 'july'
		),
		'08' => array(
			'uk' => 'серпня',
			'ru' => 'августа',
			'en' => 'august'
		),
		'09' => array(
			'uk' => 'вересня',
			'ru' => 'сентября',
			'en' => 'september'
		),
		'10' => array(
			'uk' => 'жовтня',
			'ru' => 'октября',
			'en' => 'october'
		),
		'11' => array(
			'uk' => 'листопада',
			'ru' => 'ноября',
			'en' => 'november'
		),
		'12' => array(
			'uk' => 'грудня',
			'ru' => 'декабря',
			'en' => 'december'
		)
	);		
		
	public function dateTranslator(array $data)
	{
		return date('d', $data['timestamp'])
				. ' ' . $this->_month[date('m', $data['timestamp'])][$data['lang']]
				. ' ' . date('Y', $data['timestamp']);
	}
}