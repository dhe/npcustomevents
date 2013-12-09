<?php
/**
 * Namespace
 */
namespace neckarpixel\npcustomevents;

class customevent extends \ContentElement
{
	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'ce_npcustomevent';


	/**
	 * Display a wildcard in the back end
	 * @return string
	 */
	public function generate() {
		if (TL_MODE == 'BE')
		{
			// GET EVENT
			$objEvent = \CalendarEventsModel::findPublishedByParentAndIdOrAlias($this->customselectedevent,array($this->customeventcalendar));
			
			//GET RELATED
			$objArchive = $objEvent->getRelated('pid');
			
			$objTemplate = new \BackendTemplate('be_wildcard');

			$objTemplate->wildcard  = '### Custom Event ### <br /><br />';
			$objTemplate->wildcard .= 'Event: '.$objEvent->title.' [ID '.$objEvent->id.']<br>';
			$objTemplate->wildcard .= 'Kalender: '.$objArchive->title;
			
			$objTemplate->title = $this->headline;
			/*$objTemplate->id = $this->id;
			$objTemplate->link = $this->headline;
			$objTemplate->href = 'contao/main.php?do=article&amp;table=tl_content&amp;act=edit&amp;id=' . $this->id;*/

			return $objTemplate->parse();
		}

		return parent::generate();
	}
	
	/**
	 * Generate the CTE
	 */
	protected function compile() {

		if($this->customeventcalendar && $this->customselectedevent) {
		
		$objEvent = \CalendarEventsModel::findPublishedByParentAndIdOrAlias($this->customselectedevent,array($this->customeventcalendar));
		$arrEvents = $objEvent->row();
		$limit = 1;
		$offset = 0;
		
		// Parse events
		for ($i=$offset; $i<$limit; $i++)
		{
			$event = $arrEvents;
			
			$objCalendar = \CalendarModel::findByPk($event['pid']);

			// Get the current "jumpTo" page
			if ($objCalendar !== null && $objCalendar->jumpTo && ($objTarget = $objCalendar->getRelated('jumpTo')) !== null)
			{
				$strUrl = $this->generateFrontendUrl($objTarget->row(), ($GLOBALS['TL_CONFIG']['useAutoItem'] ?  '/%s' : '/events/%s'));
			}

			$blnIsLastEvent = false;
			$event['firstDay'] = $GLOBALS['TL_LANG']['DAYS'][date('w', $event['startDate'])];
			$event['firstDate'] = \Date::parse($objPage->dateFormat, $event['startDate']);
			$event['datetime'] = date('Y-m-d', $event['startDate']);
			$event['href'] = ampersand(sprintf($strUrl, ((!$GLOBALS['TL_CONFIG']['disableAlias'] && $event['alias'] != '') ? $event['alias'] : $event['id'])));

			// Last event on the current day
			/*if (($i+1) == $limit || !isset($arrEvents[($i+1)]['firstDate']) || $event['firstDate'] != $arrEvents[($i+1)]['firstDate'])
			{
				$blnIsLastEvent = true;
			}*/

			//$objTemplate = new \FrontendTemplate($this->np_ce_template);
			$objTemplate = new \FrontendTemplate('event_teaser');
			$objTemplate->setData($event);

			// Month header
			if ($strMonth != $event['month'])
			{
				$objTemplate->newMonth = true;
				$strMonth = $event['month'];
			}

			// Day header
			if ($strDate != $event['firstDate'])
			{
				$headerCount = 0;
				$objTemplate->header = true;
				$objTemplate->classHeader = ((($dayCount % 2) == 0) ? ' even' : ' odd') . (($dayCount == 0) ? ' first' : '') . (($event['firstDate'] == $arrEvents[($limit-1)]['firstDate']) ? ' last' : '');
				$strDate = $event['firstDate'];

				++$dayCount;
			}

			// Add template variables
			$objTemplate->classList = $event['class'] . ((($headerCount % 2) == 0) ? ' even' : ' odd') . (($headerCount == 0) ? ' first' : '') . ($blnIsLastEvent ? ' last' : '') . ' cal_' . $event['pid'];
			$objTemplate->classUpcoming = $event['class'] . ((($eventCount % 2) == 0) ? ' even' : ' odd') . (($eventCount == 0) ? ' first' : '') . ((($offset + $eventCount + 1) >= $limit) ? ' last' : '') . ' cal_' . $event['pid'];
			$objTemplate->readMore = specialchars(sprintf($GLOBALS['TL_LANG']['MSC']['readMore'], $event['title']));
			$objTemplate->more = $GLOBALS['TL_LANG']['MSC']['more'];

			// Short view
			if ($this->cal_noSpan)
			{
				$objTemplate->day = $event['day'];
				$objTemplate->date = $event['date'];
				$objTemplate->span = ($event['time'] == '' && $event['day'] == '') ? $event['date'] : '';
			}
			else
			{
				$objTemplate->day = $event['firstDay'];
				$objTemplate->date = $event['firstDate'];
				$objTemplate->span = '';
			}

			$objTemplate->addImage = false;

			// Add an image
			if ($event['addImage'] && $event['singleSRC'] != '')
			{
				if (!is_numeric($event['singleSRC']))
				{
					$objTemplate->text = '<p class="error">'.$GLOBALS['TL_LANG']['ERR']['version2format'].'</p>';
				}
				else
				{
					$objModel = \FilesModel::findByPk($event['singleSRC']);

					if ($objModel !== null && is_file(TL_ROOT . '/' . $objModel->path))
					{
						if ($imgSize)
						{
							$event['size'] = $imgSize;
						}

						$event['singleSRC'] = $objModel->path;
						$this->addImageToTemplate($objTemplate, $event);
					}
				}
			}

			$objTemplate->enclosure = array();

			// Add enclosure
			if ($event['addEnclosure'])
			{
				$this->addEnclosuresToTemplate($objTemplate, $event);
			}

			$strEvents .= $objTemplate->parse();

			++$eventCount;
			++$headerCount;
		}
		// No events found
		if ($strEvents == '')
		{
			$strEvents = "\n" . '<div class="empty">' . $strEmpty . '</div>' . "\n";
		}

		// See #3672
		$this->Template->headline = $this->headline;
		$this->Template->events = $strEvents;
		}

	}

}

?>