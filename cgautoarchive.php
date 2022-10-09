<?php
/** CG Auto Archive
*
* Version			: 1.0.0
* Package			: Joomla 4.0
* copyright 		: Copyright (C) 2022 ConseilGouz. All rights reserved.
* license    		: http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*/

defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\Registry\Registry;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Component\Scheduler\Administrator\Event\ExecuteTaskEvent;
use Joomla\Component\Scheduler\Administrator\Task\Status as TaskStatus;
use Joomla\Component\Scheduler\Administrator\Traits\TaskPluginTrait;
use Joomla\Event\SubscriberInterface;

class PlgTaskCGAutoArchive extends CMSPlugin implements SubscriberInterface
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
	protected $myparams;

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

		$db = Factory::getDbo();
		try {
			$query = $db->getQuery(true);
			$query->update("#__content")
			->set('state =2,publish_down = null')
			->where('publish_down < now() AND state IN (0, 1, 2)');
			$db->setQuery($query);
			$db->execute();
		}	catch ( Exception $e ) {
			return TaskStatus::INVALID_EXIT ;
		}
		return TaskStatus::OK;
	}
}