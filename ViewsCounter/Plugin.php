<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
/**
 * 文章浏览量统计插件，可设置对同一篇文章的多次浏览行为是否计入浏览量的时间间隔。
 *
 * @package ViewsCounter
 * @author  Quarkay
 * @version 1.0.0
 * @link https://www.quarkay.com
 */
class ViewsCounter_Plugin implements Typecho_Plugin_Interface
{
    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     *
     * @throws Typecho_Db_Exception
     */
    public static function activate()
    {
        Typecho_Plugin::factory('Widget_Archive')->beforeRender = array(
            'ViewsCounter_Plugin',
            'count'
        );

        // 修改contents表，添加views字段
        $db = Typecho_Db::get();
        $prefix = $db->getPrefix();
        if (!array_key_exists(
            'views',
            $db->fetchRow($db->select()->from('table.contents'))))
            $db->query(
                'ALTER TABLE `' . $prefix
                . 'contents` ADD `views` INT DEFAULT 0;'
            );
    }

    /**
     * 禁用插件方法,如果禁用失败,直接抛出异常
     *
     * @access public
     * @return void
     */
    public static function deactivate(){}

    /**
     * 获取插件配置面板
     *
     * @access public
     * @param Typecho_Widget_Helper_Form $form 配置面板
     * @return void
     */
    public static function config(Typecho_Widget_Helper_Form $form){
//        $think_transaction = new Typecho_Widget_Helper_Form_Element_Radio(
//            'think_transaction', array(
//            '0' => '不考虑并发',
//            '1' => '考虑并发',
//        ), '0', '访问次数统计设置', '考虑并发会带来更大的运行负担，但统计结果更精准');
        $popular_limit = new Typecho_Widget_Helper_Form_Element_Text(
            'popular_limit',
            NULL,
            10,
            _t('浏览最多的文章输出条数')
        );
        $cookie_time = new Typecho_Widget_Helper_Form_Element_Text(
            'cookie_time',
            NULL,
            3600,
            _t('对同一篇文章的多次浏览行为是否计入浏览量的时间间隔（单位为秒）')
        );
//        $form->addInput($think_transaction);
        $form->addInput($popular_limit);
        $form->addInput($cookie_time);
    }

    /**
     * 个人用户的配置面板
     *
     * @access public
     * @param Typecho_Widget_Helper_Form $form
     * @return void
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form){}

    /**
     * 执行统计过程
     *
     * @access public
     * @param Widget_Archive $archive_obj
     * @return void
     * @throws  Typecho_Exception
     */
    public static function count($archive_obj)
    {
        // 若已登录不执行统计操作
        if (Typecho_Widget::widget('Widget_User')->hasLogin()) {
            return;
        }
        
        // 仅对文章进行统计
        if ($archive_obj->is('single')) {
            $cid = $archive_obj->cid;
            $key = '__viewsCounter';
            $cids = Typecho_Cookie::get($key);
            $cookie_time = Typecho_Widget::widget('Widget_Options')
                    ->plugin('ViewsCounter')->cookie_time + 0;

            // 仅对新访问的文章进行统计更新，否则直接返回不执行任何操作
            if (!(is_null($cids) || !in_array("{$cid}", explode(',', $cids)))) {
                return;
            }

            $db = Typecho_Db::get();
            $row = $db->fetchRow(
                $db->select('views')->from('table.contents')
                    ->where('cid = ?', $cid)
            );

            // 限制于Typecho的DB封装层，插件暂不考虑并发的情况
            $db->query(
                $db->update('table.contents')->rows(
                    array('views' => (int)$row['views']+1)
                )
                    ->where('cid = ?', $cid)
            );
            $new_cids = is_null($cids) ? $cid :
                implode(',', array_merge(explode(',', $cids), [$cid]));

            // 使用Cookie进行记录
            Typecho_Cookie::set($key, $new_cids, time()+$cookie_time);
        }
    }

    /**
     * 获取文章浏览次数的接口，传入 cid
     *
     * @access public
     * @return int
     * @throws
     */
    public static function getViewsById($cid)
    {
        $db = Typecho_Db::get();
        $row = $db->fetchRow(
            $db->select('views')->from('table.contents')->where('cid = ?', $cid)
        );
        return $row['views'];
    }

    /**
     * 获取文章浏览次数的接口，用于在主题中直接调用
     *
     * @access public
     * @return int
     * @throws
     */
    public static function getViews()
    {
        return self::getViewsById(Typecho_Widget::widget('Widget_Archive')->cid);
    }

    /**
     * 以数组形式返回最受欢迎（访问最多）的文章列表
     *
     * @return array
     * @throws
     */
    public static function getMostViewed()
    {
        $db = Typecho_Db::get();
        $limit = Typecho_Widget::widget('Widget_Options')
                ->plugin('ViewsCounter')->popular_limit + 0;
        $posts = $db->fetchAll(
            $db->select()->from('table.contents')->where(
                'type = ? AND status = ? AND password IS NULL',
                'post',
                'publish'
            )
                ->order('views', Typecho_Db::SORT_DESC)
                ->limit($limit)
        );
        // 包装更完善的信息输入
        $popular_list = [];
        foreach ($posts as $post) {
            $popular_list[] = Typecho_Widget::widget('Widget_Abstract_Contents')
                              ->push($post);
        }
        return $popular_list;
    }
}
