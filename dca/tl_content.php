<?php

/**
 * CustomEvents
 * 
 * @package   npcustomevents
 * @author    neckarpixel David Hestler
 * @license   GPL
 * @copyright neckarpixel David Hestler 2013
 */

// SELECTOR
$GLOBALS['TL_DCA']['tl_content']['palettes']['__selector__'][] = 'customeventcalendar';

// Palettes
$GLOBALS['TL_DCA']['tl_content']['palettes']['customevent'] = '{type_legend},type,headline;{customevent_legend},customeventcalendar,customselectedevent;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';

$GLOBALS['TL_DCA']['tl_content']['palettes']['customeventlist'] = '{type_legend},type,headline;{customeventlist_legend},customevents,np_ce_template;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';

// Subpalettes


// Fields
$GLOBALS['TL_DCA']['tl_content']['fields']['customeventcalendar'] = array(
	'label'					=> &$GLOBALS['TL_LANG']['tl_content']['cal'],
	'inputType'				=> 'select',
	'foreignKey'			=> 'tl_calendar.title',
	'eval'					=> array(	'submitOnChange'=>true,'includeBlankOption' => true, 'tl_class' => 'w50'),
	'sql'					=> "varchar(32) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_content']['fields']['customselectedevent'] = array(
	'label'					=>&$GLOBALS['TL_LANG']['tl_content']['event'],
	'inputType'				=> 'select',
	'options_callback'		=> array('tl_content_np_ce', 'getEvents'),
	'eval'					=> array('tl_class' => 'w50','includeBlankOption' => true),
	'sql'					=> "varchar(32) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_content']['fields']['np_ce_template'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_content']['np_ce_template'],
	'default'                 => 'cal_default',
	'exclude'                 => true,
	'inputType'               => 'select',
	'options_callback'        => array('tl_content_np_ce', 'getEventTemplates'),
	'eval'                    => array('tl_class'=>'clr'),
	'sql'                     => "varchar(32) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_content']['fields']['customevents'] = array
(
	'label'					=> &$GLOBALS['TL_LANG']['tl_content']['customevents'],
	'exclude' 				=> true,
	'inputType' 			=> 'multiColumnWizard',
	'sql'                   => "blob NULL",
	'eval' 					=> array
	(
		'columnFields'=> array(
            'cal'=> array(
                'label'=>&$GLOBALS['TL_LANG']['tl_content']['cal'],
                'inputType'=>'select',
				'foreignKey' => 'tl_calendar.title',
                'eval'=> array(
                    'style'=>'width:200px',
                    'submitOnChange'=>true,
					'includeBlankOption' => true,
					'chosen'=>'true'
                )
            ),
            'event'=> array(
                'label'=>&$GLOBALS['TL_LANG']['tl_content']['event'],
                'inputType'=>'select',
				'onload_callback'		=> array('tl_content_np_ce', 'callbackLoad'),
				'options_callback'		=> array('tl_content_np_ce', 'callbackOptions'),
                'eval'=> array(
                    'style'=>'width:400px',
					'submitOnChange'=>true,
					'chosen'=>'true',
					'isAssociative'=>true
                )
            )
        )
	)
);

class tl_content_np_ce extends \Backend {
	
	/**
	 * Import the back end user object
	 */
	public function __construct()
	{
		parent::__construct();
		$this->import('BackendUser', 'User');
	}
	
	protected static $instance;
	protected $mcw;
 
	public static function getInstance()
	{
		if(!isset(static::$instance)) {
			static::$instance = new static;
		}
 
		return static::$instance;
	}
 
 
	/**
	 * Called via load_callback of options field
	 * @param $value
	 * @param $mcw
	 * @return mixed
	 */
	public function callbackLoad($value, $mcw)
	{
		$this->mcw = $mcw;
		print_r($mcw);
		return $value;
	}
 
 
	/**
	 * Called via options_callback
	 */
	public function callbackOptions()
	{
		// get column for current row
		$value = $this->mcw->value[$this->index]['column'];

		$arrOutputAttr = array();
		$objEvents = \CalendarEventsModel::findPublishedDefaultByPid($value,array());
		if ($objEvents !== null){
			while ($objEvents->next()){
				//if ($this->User->isAdmin || $this->User->hasAccess($objEvents->id, 'calendar')){
					$arrOutputAttr[$objEvents->id] = $objEvents->title . ' (ID '.$objEvents->id.')';
				//}
			}
		}
		// raise index. be aware of invalid index which happens because 
		// DC_Table goes throw all widgets twice, first by saving values
		// and by creating the view
		$this->index++;
 
		if(!isset($this->mcw->value[$this->index])) {
			$this->index = 0;
		}
		
		return $arrOutputAttr;
	}
 
	
	
   /* public function getValues(MultiColumnWizard $mcw){

		
		$arrOutputAttr = array();
		$objEvents = \CalendarEventsModel::findPublishedDefaultByPid($mcw->value[$mcw->activeRow]['cal'],array());
		if ($objEvents !== null){
			while ($objEvents->next()){
				//if ($this->User->isAdmin || $this->User->hasAccess($objEvents->id, 'calendar')){
					$arrOutputAttr[$objEvents->id] = $objEvents->title . ' (ID '.$objEvents->id.')';
				//}
			}
		}
		
        return $arrOutputAttr;
    }*/
	
	public function getEvents(DataContainer $dc){
		$arrOutputAttr = array();
		$objEvents = \CalendarEventsModel::findPublishedDefaultByPid($dc->activeRecord->customeventcalendar,array());
		if ($objEvents !== null){
			while ($objEvents->next()){
				//if ($this->User->isAdmin || $this->User->hasAccess($objEvents->id, 'calendar')){
					if($objEvents->endDate > $objEvents->startDate) {
						$date = date('d.m.Y',$objEvents->startDate). ' - '.date('d.m.Y',$objEvents->endDate);
					} else {
						$date = date('d.m.Y',$objEvents->startDate);
					}
					$arrOutputAttr[$objEvents->id] = $objEvents->title . ' (Datum: '.$date.')';
				//}				
			}
		}
		
        return $arrOutputAttr;
	}
	
	/**
	 * Return all event templates as array
	 * @return array
	 */
	public function getEventTemplates()
	{
		return $this->getTemplateGroup('event_');
	}
	
}


?>
