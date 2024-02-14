<?php
/** CG Auto Archive
*
* Version			: 2.0.1
* Package			: Joomla 4.x/5.x
* copyright 		: Copyright (C) 2024 ConseilGouz. All rights reserved.
* license    		: https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
*/
namespace ConseilGouz\Plugin\Task\CGAutoArchive\Extension;

defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use RuntimeException;
use Joomla\Registry\Registry;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Component\Scheduler\Administrator\Event\ExecuteTaskEvent;
use Joomla\Component\Scheduler\Administrator\Task\Status as TaskStatus;
use Joomla\Component\Scheduler\Administrator\Traits\TaskPluginTrait;
use Joomla\Event\SubscriberInterface;
use Joomla\Event\DispatcherInterface;

class Cgautoarchive extends CMSPlugin implements SubscriberInterface
{
	use TaskPluginTrait;
	/**
	 * @var boolean
	 * @since 4.1.0
	 */
	protected $autoloadLanguage = true;
	/**
	 * @var string[]
	 *
	 * @since 4.1.0
	 */
	protected const TASKS_MAP = [
		'cgautoarchive' => [
			'langConstPrefix' => 'PLG_TASK_CGAUTOARCHIVE',
			'form'            => 'cgautoarchive',
			'method'          => 'cgautoarchive',
		],
	];
	/**
	 * @inheritDoc
	 *
	 * @return string[]
	 *
	 * @since 4.1.0
	 */
	public static function getSubscribedEvents(): array
	{
		return [
			'onTaskOptionsList'    => 'advertiseRoutines',
			'onExecuteTask'        => 'standardRoutineHandler',
			'onContentPrepareForm' => 'enhanceTaskItemForm',
		];
	}
	protected function cgautoarchive(ExecuteTaskEvent $event): int {
		$params    = $event->getArgument('params');
		$db = Factory::getDbo();
		if (($params->task_choice == 'PUBLISHED') || ($params->task_choice == 'BOTH')) { 
			try {
				$query = $db->getQuery(true);
				$query->update("#__content")
				->set('state =2')
				->where('publish_down < now() AND state IN (0, 1)');
				$db->setQuery($query);
				$db->execute();
			}	catch ( RuntimeException $e ) {
				return TaskStatus::INVALID_EXIT ;
			}
		}
		if (($params->task_choice == 'EOL') || ($params->task_choice == 'BOTH')) { 
			$cats = $params->categories;
			if (!is_array($cats)) $cats = [];
			try {
				$query = $db->getQuery(true);
				$query->update("#__content")
				->set('state =2')
				->where('publish_up <= DATE_SUB(now(),INTERVAL '.$params->sqlinterval.' '.$params->sqldatetype.') AND state IN (0, 1)');
				if (sizeOf($cats))	$query->where('catid in ('.implode(",",$cats).')');
				$db->setQuery($query);
				$db->execute();
			}	catch ( RuntimeException $e ) {
				return TaskStatus::INVALID_EXIT ;
			}
		}
		return TaskStatus::OK;
	}
}