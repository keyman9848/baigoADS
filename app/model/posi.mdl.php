<?php
/*-----------------------------------------------------------------
！！！！警告！！！！
以下为系统文件，请勿修改
-----------------------------------------------------------------*/

namespace app\model;

use app\classes\Model;
use ginkgo\Func;
use ginkgo\Cache;
use ginkgo\Json;
use ginkgo\Config;
use ginkgo\File;

//不能非法包含或直接执行
defined('IN_GINKGO') or exit('Access denied');

/*-------------广告位类-------------*/
class Posi extends Model {

    public $arr_status      = array('enable', 'disabled');
    public $arr_isPercent   = array('enable', 'disabled');

    function m_init() { //构造函数
        $_arr_configRoute  = Config::get('route', 'index');

        $this->obj_file     = File::instance();
        $this->obj_cache    = Cache::instance();

        if (!isset($_arr_configRoute['posi'])) {
            $_arr_configRoute['posi'] = '';
        }

        $this->urlPrefix    = $this->obj_request->baseUrl(true) . $_arr_configRoute['posi'] . '/';
    }


    function check($mix_posi, $str_by = 'posi_id', $num_notId = 0) {
        $_arr_posiSelect = array(
            'posi_id',
        );

        return $this->readProcess($mix_posi, $str_by, $num_notId, $_arr_posiSelect);
    }



    /**
     * read function.
     *
     * @access public
     * @param mixed $mix_posi
     * @param string $str_by (default: 'posi_id')
     * @param int $num_notId (default: 0)
     * @return void
     */
    function read($mix_posi, $str_by = 'posi_id', $num_notId = 0, $arr_select = array()) {
        $_arr_posiRow = $this->readProcess($mix_posi, $str_by, $num_notId, $_arr_posiSelect);

        if ($_arr_posiRow['rcode'] != 'y040102') {
            return $_arr_posiRow;
        }

        return $this->rowProcess($_arr_posiRow);
    }


    function readProcess($mix_posi, $str_by = 'posi_id', $num_notId = 0, $arr_select = array()) {
        if (Func::isEmpty($arr_select)) {
            $arr_select = array(
                'posi_id',
                'posi_name',
                'posi_count',
                'posi_status',
                'posi_script',
                'posi_box_perfix',
                'posi_loading',
                'posi_close',
                'posi_opts',
                'posi_is_percent',
                'posi_note',
            );
        }

        $_arr_where = $this->readQueryProcess($mix_posi, $str_by, $num_notId);

        $_arr_posiRow = $this->where($_arr_where)->find($arr_select);

        if (!$_arr_posiRow) {
            return array(
                'msg'   => 'Position not found',
                'rcode' => 'x040102', //不存在记录
            );
        }

        $_arr_posiRow['rcode'] = 'y040102';
        $_arr_posiRow['msg']   = '';

        return $_arr_posiRow;
    }


    /**
     * list function.
     *
     * @access public
     * @param mixed $num_no
     * @param int $num_except (default: 0)
     * @param string $str_key (default: '')
     * @param string $str_type (default: '')
     * @return void
     */
    function lists($num_no, $num_except = 0, $arr_search = array()) {

        $_arr_posiSelect = array(
            'posi_id',
            'posi_name',
            'posi_count',
            'posi_status',
            'posi_script',
            'posi_box_perfix',
            'posi_loading',
            'posi_close',
            'posi_is_percent',
            'posi_note',
        );

        $_arr_where = $this->queryProcess($arr_search);

        $_arr_posiRows = $this->where($_arr_where)->order('posi_id', 'DESC')->limit($num_except, $num_no)->select($_arr_posiSelect);

        //print_r($_arr_posiRows);

        return $_arr_posiRows;

    }


    /**
     * count function.
     *
     * @access public
     * @param string $str_key (default: '')
     * @param string $str_status (default: '')
     * @return void
     */
    function count($arr_search = array()) {
        $_arr_where = $this->queryProcess($arr_search);

        $_num_count = $this->where($_arr_where)->count(); //查询数据

        return $_num_count;
    }


    protected function queryProcess($arr_search = array()) {
        $_arr_where = array();

        if (isset($arr_search['key']) && !Func::isEmpty($arr_search['key'])) {
            $_arr_where[] = array('posi_name|posi_note', 'LIKE', '%' . $arr_search['key'] . '%', 'key');
        }

        if (isset($arr_search['status']) && !Func::isEmpty($arr_search['status'])) {
            $_arr_where[] = array('posi_status', '=', $arr_search['status']);
        }

        return $_arr_where;
    }


    function readQueryProcess($mix_posi, $str_by = 'posi_id', $num_notId = 0) {
        $_arr_where[] = array($str_by, '=', $mix_posi);

        if ($num_notId > 0) {
            $_arr_where[] = array('posi_id', '<>', $num_notId);
        }

        return $_arr_where;
    }


    function scriptConfigProcess($str_dir) {
        $_str_configPath = BG_PATH_ADVERT . $str_dir . DS . 'config.json';

        if (Func::isFile($_str_configPath)) {
            $_str_scriptConfig = $this->obj_file->fileRead($_str_configPath); //定义配置
            $_arr_scriptConfig = Json::decode($_str_scriptConfig);
        } else {
            $_arr_scriptConfig = array();
        }

        if (!isset($_arr_scriptConfig['script_name']) || Func::isEmpty($_arr_scriptConfig['script_name'])) {
            $_arr_scriptConfig['script_name'] = $str_dir;
        }

        if (!isset($_arr_scriptConfig['css_name'])) {
            $_arr_scriptConfig['css_name'] = $str_dir;
        }

        if (!isset($_arr_scriptConfig['name'])) {
            $_arr_scriptConfig['name'] = $str_dir;
        }

        if (!isset($_arr_scriptConfig['require'])) {
            $_arr_scriptConfig['require'] = array();
        }

        if (!isset($_arr_scriptConfig['func_init']) || Func::isEmpty($_arr_scriptConfig['func_init'])) {
            $_arr_scriptConfig['func_init'] = 'ads' . Func::toHump($str_dir, '_');
        }

        if (!isset($_arr_scriptConfig['box_perfix']) || Func::isEmpty($_arr_scriptConfig['box_perfix'])) {
            $_arr_scriptConfig['box_perfix'] = '#ads-' . strtolower($str_dir);
        }

        if (!isset($_arr_scriptConfig['is_percent'])) {
            $_arr_scriptConfig['is_percent'] = 'enable';
        }

        if (!isset($_arr_scriptConfig['count']) || Func::isEmpty($_arr_scriptConfig['count'])) {
            $_arr_scriptConfig['count'] = 1;
        }

        if (!isset($_arr_scriptConfig['loading'])) {
            $_arr_scriptConfig['loading'] = '';
        }

        if (!isset($_arr_scriptConfig['close'])) {
            $_arr_scriptConfig['close'] = '';
        }

        if (!isset($_arr_scriptConfig['note'])) {
            $_arr_scriptConfig['note'] = '';
        }

        if (strpos($_arr_scriptConfig['box_perfix'], '#') === false && strpos($_arr_scriptConfig['box_perfix'], '.') === false) {
            $_arr_scriptConfig['box_perfix'] = '#' . $_arr_scriptConfig['box_perfix'];
        }

        if (strpos($_arr_scriptConfig['script_name'], '.js') === false) {
            $_arr_scriptConfig['script_name'] .= '.min.js';
        }

        if (strpos($_arr_scriptConfig['css_name'], '.css') === false) {
            $_arr_scriptConfig['css_name'] .= '.css';
        }

        foreach ($_arr_scriptConfig['require'] as $_key=>$_value) {
            if (is_array($_value)) {
                if (!isset($_value['url'])) {
                    $_value['url'] = '';
                }

                if (!isset($_value['type']) || $_value['type'] == 'auto') {
                    if (Func::isEmpty($_value['url'])) {
                        $_value['type'] = 'css';
                    } else {
                        $_str_ext = pathinfo($_value['url'], PATHINFO_EXTENSION);
                        $_str_ext = strtolower($_str_ext);

                        switch ($_str_ext) {
                            case 'js':
                                $_value['type'] = 'js';
                            break;

                            default:
                                $_value['type'] = 'css';
                            break;
                        }
                    }
                } else {
                    $_value['type'] = 'css';
                }

                $_arr_scriptConfig['require'][$_key]  = $_value;
            } else if (is_scalar($_value)) {
                if (Func::isEmpty($_value)) {
                    $_value['type'] = 'css';
                } else {
                    $_str_ext = pathinfo($_value, PATHINFO_EXTENSION);
                    $_str_ext = strtolower($_str_ext);

                    switch ($_str_ext) {
                        case 'js':
                            $_str_type = 'js';
                        break;

                        default:
                            $_str_type = 'css';
                        break;
                    }
                }

                $_arr_scriptConfig['require'][$_key] = array(
                    'url'  => $_value,
                    'type' => $_str_type,
                );
            }
        }

        $_arr_scriptConfig['script_path']       = BG_PATH_ADVERT . $str_dir . DS . $_arr_scriptConfig['script_name'];
        $_arr_scriptConfig['script_url_name']   = $str_dir . '/' . $_arr_scriptConfig['script_name'];
        $_arr_scriptConfig['css_url_name']      = $str_dir . '/' . $_arr_scriptConfig['css_name'];
        $_arr_scriptConfig['opts_path']         = BG_PATH_ADVERT . $str_dir . DS . 'opts.json';

        return $_arr_scriptConfig;
    }


    function scriptOptsProcess($str_optsPath) {
        if (Func::isFile($str_optsPath)) {
            $_str_scriptOpts = $this->obj_file->fileRead($str_optsPath); //定义配置
            $_arr_scriptOpts = Json::decode($_str_scriptOpts);
        } else {
            $_arr_scriptOpts = array();
        }

        if (!Func::isEmpty($_arr_scriptOpts)) {
            foreach ($_arr_scriptOpts as $_key=>$_value) {
                if (!isset($_value['title'])) {
                    $_value['title'] = $_key;
                }

                if (!isset($_value['var_default'])) {
                    $_value['var_default'] = '';
                }

                if (!isset($_value['type'])) {
                    $_value['type'] = 'text';
                }

                switch ($_value['type']) {
                    case 'select':
                        if (!isset($_value['option'])) {
                            $_value['option'] = array();
                        }
                    break;
                }

                $_arr_scriptOpts[$_key] = array_replace_recursive($_arr_scriptOpts[$_key], $_value);
            }
        }

        return $_arr_scriptOpts;
    }


    protected function rowProcess($arr_posiRow = array()) {
        if (!isset($arr_posiRow['posi_box_perfix']) || Func::isEmpty($arr_posiRow['posi_box_perfix'])) {
            $arr_posiRow['posi_box_perfix'] = '#ads-' . strtolower($arr_posiRow['posi_script']);
        }

        if (isset($arr_posiRow['posi_opts'])) {
            $arr_posiRow['posi_opts']      = Json::decode($arr_posiRow['posi_opts']); //json解码
        } else {
            $arr_posiRow['posi_opts']      = array();
        }

        if (strpos($arr_posiRow['posi_box_perfix'], '#') === false && strpos($arr_posiRow['posi_box_perfix'], '.') === false) {
            $arr_posiRow['posi_box_perfix'] = '#' . $arr_posiRow['posi_box_perfix'];
        }

        $arr_posiRow['posi_selector'] = $arr_posiRow['posi_box_perfix'] . '_' . $arr_posiRow['posi_id'];

        $_str_selectorType = substr($arr_posiRow['posi_box_perfix'], 0, 1);

        switch ($_str_selectorType) {
            case '.';
                $arr_posiRow['posi_box_attr'] = 'class=&quot;' . str_replace('.', '', $arr_posiRow['posi_selector']) . '&quot;';
            break;

            case '#';
                $arr_posiRow['posi_box_attr'] = 'id=&quot;' . str_replace('#', '', $arr_posiRow['posi_selector']) . '&quot;';
            break;
        }

        $arr_posiRow['posi_data_url']      = $this->urlPrefix . $arr_posiRow['posi_id'];

        return $arr_posiRow;
    }


    function cacheProcess($num_posiId) {
        $_return = 0;
        $_arr_posiRow = $this->read($num_posiId);

        if ($_arr_posiRow['rcode'] == 'y040102') {
            $_return = $this->obj_cache->write('posi_' . $num_posiId, $_arr_posiRow);
        }

        return $_return;
    }


    function cacheListsProcess() {
        $_arr_search = array(
            'status'    => 'enable',
        );

        $_arr_posiRows = $this->lists(1000, 0, $_arr_search);

        return $this->obj_cache->write('posi_lists', $_arr_posiRows);
    }
}
