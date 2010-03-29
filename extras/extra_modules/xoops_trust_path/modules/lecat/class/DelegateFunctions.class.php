<?php
/**
 * @package lecat
 * @version $Id: DelegateFunctions.class.php,v 1.1 2007/05/15 02:35:07 minahito Exp $
 */

if (!defined('XOOPS_ROOT_PATH')) exit();
require_once XOOPS_ROOT_PATH.'/modules/legacy/class/interface/AbstractCategoryDelegate.class.php';
class Lecat_DelegateFunctions extends Legacy_AbstractCategoryDelegate
{
	/**
	 * getCategoryGroupList
	 *
	 * @param string[] &$grList
	 *
	 * @return	void
	 */ 
	public function getCategoryGroupList(/*** string[] ***/ &$grList)
	{
		$objs = Legacy_Utils::getModuleHandler('gr', self::_getDirname())->getObjects();
		foreach($objs as $obj){
			$grList[$obj->getShow('title')] = $obj->getShow('gr_id');
		}
	}

	/**
	 * getTitle
	 *
	 * @param string &$title
	 * @param int $catId
	 * @param string $dirname
	 *
	 * @return	void
	 */ 
	public function getTitle(/*** string ***/ &$title, /*** int ***/ $catId)
	{
		$title = Legacy_Utils::getModuleHandler('cat', self::_getDirname())->get($catId)->get('title');
	}

	/**
	 * getTree
	 *
	 * @param array $tree
	 * @param int $grId
	 * @param string $action
	 * @param int $uid
	 * @param int $catId
	 * @param string $module
	 *
	 * @return	void
	 */ 
	public function getTree(/*** array ***/ &$tree, /*** int ***/ $grId, /*** string ***/ $action, /*** int ***/ $uid, /*** int ***/ $catId=0, /*** string ***/ $module="")
	{
		$grObj = Legacy_Utils::getModuleHandler('gr', self::_getDirname())->get($grId);
		$grObj->loadTree(intval($catId));
		$grObj->filterCategory($action, $uid, false);
		$tree = $grObj->mTree;
	}

	/**
	 * getTitleList
	 *
	 * @param string &$titleList
	 * @param int $grId
	 *
	 * @return	void
	 */ 
	public function getTitleList(/*** string[] ***/ &$titleList, /*** int ***/ $grId)
	{
		$catObjs = Legacy_Utils::getModuleHandler('cat', self::_getDirname())->getObjects(new Criteria('gr_id', $grId));
		foreach(array_keys($catObjs) as $key){
			if($catObjs[$key]->checkModule()){
				$titleList[$catObjs[$key]->get('cat_id')] = $catObjs[$key]->get('title');
			}
		}
	}

	/**
	 * checkPermitByUserId
	 *
	 * @param bool &$check
	 * @param int $catId
	 * @param string $action
	 * @param int $uid
	 * @param string $module
	 *
	 * @return	void
	 */ 
	public function checkPermitByUserId(/*** bool ***/ &$check, /*** int ***/ $catId, /*** string ***/ $action, /*** int ***/ $uid, /*** string ***/ $module="")
	{
		$check = Legacy_Utils::getModuleHandler('cat', self::_getDirname())->get($catId)->checkPermitByUid($action, $uid, $module);
	}

	/**
	 * checkPermitByGroupId
	 *
	 * @param bool &$check
	 * @param int $catId
	 * @param string $action
	 * @param int $groupId
	 * @param string $module
	 *
	 * @return	void
	 */ 
	public function checkPermitByGroupId(/*** bool ***/ &$check, /*** int ***/ $catId, /*** string ***/ $action, /*** int ***/ $groupId, /*** string ***/ $module="")
	{
		$check = Legacy_Utils::getModuleHandler('cat', self::_getDirname())->get($catId)->checkPermitByGroupid($action, $groupid, $module);
	}

	/**
	 * getParent
	 *
	 * @param Lecat_CatObject &$parent
	 * @param int $catId
	 *
	 * @return	void
	 */ 
	public function getParent(/*** Lecat_CatObject ***/ &$parent, /*** int ***/ $catId)
	{
		$handler = Legacy_Utils::getModuleHandler('cat', self::_getDirname());
		$pId = $handler->get($catId)->get('p_id');
		$parent = $handler->get($pId);
	}

	/**
	 * getChildren
	 *
	 * @param array &$children
	 * @param int $catId
	 * @param string $action
	 * @param int $uid
	 * @param string $module
	 *
	 * @return	void
	 */ 
	public function getChildren(/*** array ***/ &$children, /*** int ***/ $catId, /*** string ***/ $action, /*** int ***/ $uid, /*** string ***/ $module="")
	{
		$handler = Legacy_Utils::getModuleHandler('cat', self::_getDirname());
		$cat = $handler->get($catId);
		$cat->loadChildren($module);
		foreach(array_keys($cat->mChildren) as $key){
			$children['catObj'][$key] = $cat->mChildren[$key];
			if($action){
				if($cat->mChildren[$key]->checkPermitByUserId($action, intval($uid))=='true'){
					$children['permit'][$key] = 1;
				}
				else{
					$children['permit'][$key] = 0;
				}
			}
			else{
				$children['permit'][$key] = 1;
			}
		}
	}

	/**
	 * getCatPath
	 *
	 * @param string &$catPath
	 * @param int $catId
	 * @param string $order
	 *
	 * @return	void
	 */ 
	public function getCatPath(/*** array ***/ &$catPath, /*** int ***/ $catId, /*** string ***/ $order, /*** string ***/ $module="")
	{
		$cat = Legacy_Utils::getModuleHandler('cat', self::_getDirname())->get($catId);
		$cat->loadCatPath();
		if($order=='ASC' && count($cat->mCatPath)>0){
			$catPath['cat_id'] = array_reverse($cat->mCatPath['cat_id']);
			$catPath['title'] = array_reverse($cat->mCatPath['title']);
		}
		else{
			$catPath = $cat->mCatPath;
		}
	}

	/**
	 * getPermittedIdList
	 *
	 * @param int[] &$idList
	 * @param int $grId
	 * @param string $action
	 * @param int $uid
	 * @param int $catId
	 * @param string $module
	 *
	 * @return	void
	 */ 
	public function getPermittedIdList(/*** int[] ***/ &$idList, /*** int ***/ $grId, /*** string ***/ $action, /*** int ***/ $uid, /*** int ***/ $catId=0, /*** string ***/ $module="")
	{
		if($grObj = Legacy_Utils::getModuleHandler('gr', self::_getDirname())->get($grId)){
			$grObj->loadTree(intval($catId));
			$grObj->filterCategory($action, $uid, true);
			foreach(array_keys($grObj->mTree) as $key){
				$idList[] = $grObj->mTree[$key]->get('cat_id');
			}
			unset($grObj);
		}
	}


	protected function _getDirname()
	{
		return LEGACY_CATEGORY_DIRNAME;
	}

}

?>