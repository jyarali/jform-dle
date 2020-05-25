<?php
/*
=====================================================
DataLife Engine v14.0
-----------------------------------------------------
Jform Plugin By Jamal Yarali
-----------------------------------------------------
FileName :  engine/inc/jform.php
-----------------------------------------------------
https://github.com/jyarali/jform-dle
=====================================================
 */

if (!defined('DATALIFEENGINE') or !defined('LOGGED_IN')) {
    header("HTTP/1.1 403 Forbidden");
    header('Location: ../../');
    die("Hacking attempt!");
}

define( 'SETTING', ENGINE_DIR . '/data/jform.txt' );
$setting = unserialize( file_get_contents( SETTING ) );

function jheader(){
    echo <<<HTML
    <div class="panel panel-default">
        <div class="panel-body">
            <div class="text-center">
                <div class="col-md-4 h5 text-center">
                    <a style="color: #207fbc" href="?mod=jform">
                        <i class="fa fa-wpforms" style="font-size: 5rem;"></i> لیست فرم‌ها
                    </a>
                </div>
                <div class="col-md-4 h5 text-center">
                    <a style="color: #207fbc" href="?mod=jform&action=new">
                        <i class="fa fa-pencil" style="font-size: 5rem;"></i> افزودن فرم جدید
                    </a>
                </div>
                <div class="col-md-4 h5 text-center">
                    <a style="color: #207fbc" href="?mod=jform&action=setting">
                        <i class="fa fa-gears" style="font-size: 5rem;"></i> تنظیمات
                    </a>
                </div>
            </div>
        </div>
    </div>
HTML;
}
function copyright(){
$text =  <<<HTML
    <div class="alert alert-info alert-styled-left alert-arrow-left alert-component">
        پلاگین فرمساز Jform<br>
        برای بیان پیشنهادات و مشکلات خود در مورد این پلاگین می توانید به آدرس  گیت هاب پروژه مراجعه کنید: <a target="_blank" href="https://github.com/jyarali/jform-dle">Jform DLE Form Generator</a>
        <br>
        برای حمایت از این پروژه لطفا در گیت هاب به پروژه ستاره بدین و اگه دوست داشتین، میتونید با استفاده از لینک مقابل و با پرداخت مبلغی دلخواه از نویسنده پلاگین حمایت کنید:
        <a target="_blank" href="https://idpay.ir/jyarali">Donate</a>
        <div style="direction:ltr;position:absolute;top:5px;left:5px;">
            <a class="github-button" href="https://github.com/jyarali/jform-dle" data-color-scheme="no-preference: light; light: light; dark: dark;" data-icon="octicon-star" data-size="large" data-show-count="true" aria-label="Star jyarali/jform-dle on GitHub">Star</a>
            <script async defer src="https://buttons.github.io/buttons.js"></script>
        </div>
    </div>
HTML;
    echo $text;
}

if (!$action) {
    $action = "list";
}

if ($action == "list") {
    $js_array[] = 'engine/skins/javascripts/jquery.dataTables.js';
    $js_array[] = 'engine/skins/javascripts/dataTables.bootstrap.min.js';
    $css_array[] = 'engine/skins/stylesheets/dataTables.bootstrap4.min.css';

    echoheader("<i class=\"fa fa-file-text-o position-left\"></i><span class=\"text-semibold\">پلاگین فرمساز</span>", 'لیست فرم‌‌ها');
    
    $db->query("SELECT j.*,u.name,u.user_id,jd.read_count,jd2.count FROM " . PREFIX . "_jform j LEFT JOIN " . PREFIX . "_users u ON (j.user_id=u.user_id) LEFT JOIN " .
    "( SELECT msg_read,form_id, COUNT(form_id) AS read_count FROM " . PREFIX . "_jform_data GROUP BY form_id,msg_read HAVING msg_read=1) jd ON (jd.form_id=j.id) " .
    "LEFT JOIN ( SELECT form_id, COUNT(form_id) AS count FROM " . PREFIX . "_jform_data GROUP BY form_id) jd2 ON (jd2.form_id=j.id) " .
    "ORDER BY date DESC");
    $items = "";
    while ( $row = $db->get_array() ) {
        $row['active'] = $row['active'] == 1 ? "<span class=\"tip text-success\" data-original-title=\"فعال\"><b><i class=\"fa fa-check-circle\"></i></b></span>":"<span class=\"tip text-danger\" data-original-title=\"غیرفعال\"><b><i class=\"fa fa-exclamation-circle\"></i></b></span>";
        $row['date'] = jdate('Y/m/d', strtotime( $row['date']));
        $row['read_count'] = ($row['read_count'] == null ? 0 : $row['read_count']);
        if ($setting['seo']){
            $alt_name = fatotranslit( preg_replace("/[^\x{0600}-\x{06FF}a-zA-Z0-9_.;»«-]/u", "-", $row['title']), true, false );
            $form_url = "{$config['http_home_url']}jform/{$row['id']}-{$alt_name}.html";
        } else {
            $form_url = "{$config['http_home_url']}index.php?do=jform&amp;formid={$row['id']}";
        }
        $items .="<tr>
        <td>{$row['id']}</td>
        <td>{$row['title']}</td>
        <td class=\"text-center\">{$row['active']}</td>
        <td>{$row['name']}</td>
        <td>{$row['date']}</td>
        <td class=\"text-center\">{$row['count']} ({$row['read_count']})</td>
        <td class=\"text-center\">
            <a href=\"{$PHP_SELF}?mod=jform&amp;action=msg_short&amp;formid={$row['id']}\" class=\"tip text-teal\" data-original-title=\"لیست پیام ها\"><i class=\"fa fa-envelope\"></i></a>&nbsp;&nbsp;
            <a href=\"{$PHP_SELF}?mod=jform&amp;action=editform&amp;formid={$row['id']}\" class=\"tip text-blue\" data-original-title=\"ویرایش فرم\"><i class=\"fa fa-pencil\"></i></a>&nbsp;&nbsp;
            <a href=\"#\" onclick=\"javascript:Remove('{$row['id']}'); return false;\" class=\"tip text-danger\" data-original-title=\"حذف فرم\"><i class=\"fa fa-trash\"></i></a>&nbsp;&nbsp;
            <a href=\"{$form_url}\" target=\"_blank\" class=\"tip text-orange\" data-original-title=\"نمایش فرم در سایت\"><i class=\"fa fa-desktop\"></i></a>&nbsp;&nbsp;			
        </td>
        </tr>";
    }
    $db->free();
    
    jheader();
    echo <<<HTML
    <div class="panel panel-default">
        <div class="panel-heading">
            لیست فرم‌ها
        </div>
        <div class="panel-body">
        <table id="jlist" class="table table-striped table-xs table-hover">
        <thead>
            <tr>
                <th class="hidden-xs hidden-sm" style="width: 60px;">شماره</th>
                <th>عنوان</th>
                <th class="text-center" style="width: 90px;">وضعیت</th>
                <th style="width: 160px;">ایجاد کننده</th>
                <th style="width: 160px;">تاریخ ایجاد</th>
                <th class="text-center" style="width: 160px;">پیام‌ها (خوانده شده)</th>
                <th class="text-center" style="width: 180px;">
                    عملیات
                </th>
            </tr>
        </thead>
            <tbody>
                {$items}
            </tbody>
        </table>
        </div>
        <!-- <div class="panel-footer">
            <input type="submit" class="btn bg-teal btn-sm btn-raised position-left legitRipple" value="ارسال">
        </div> -->
    </div>
HTML;
copyright();
    echo <<<JSCRIPT
    <script>
    $(document).ready( function () {
        $('#jlist').DataTable({
            "language": {
                "url": "https://cdn.datatables.net/plug-ins/1.10.20/i18n/Persian.json"
            }
        });
    });
    $('#jlist').one('draw.dt', function () {
        $('#jlist_paginate').css('text-align', 'left');
    });
    function Remove( id ){
        DLEconfirm('آیا از حذف این فرم مطمئن هستید؟', 'پیام', function(){
          window.location = "{$PHP_SELF}?mod=jform&action=delete&formid=" + id;
        });
        return false;
    }
    </script>
JSCRIPT;
    echofooter();
}

if ($action == "new") {
    if (!in_array($member_id['user_group'],$setting['access_group'])){
        msg( "error", $lang['index_denied'], $lang['index_denied'] );
    }
    $js_array[] = 'engine/classes/js/jquery-ui.min.js';
    $js_array[] = 'engine/classes/js/form-builder.min.js';
    $js_array[] = 'engine/classes/js/form-render.min.js';
    echoheader("<i class=\"fa fa-file-text-o position-left\"></i><span class=\"text-semibold\">پلاگین فرمساز</span>", 'افزودن فرم جدید');
    $groups = '';
    $access_groups ='';
    foreach($user_group as $item){
        $groups .= $item['id'] . ':' . '"' . $item['group_name'] . '"' . ',';
        $selected = ($item['id'] == 1 ? 'selected':'');
        $access_groups .= "<option value=\"{$item['id']}\" {$selected}>{$item['group_name']}</option>";
    }
    
    jheader();
    echo <<<HTML
    <style>
        #fb-rendered-form {
        clear:both;
        display:none;
        }
    </style>
    <div class="panel panel-default">
        <div class="panel-heading">
        افزودن فرم
        </div>
        <div class="panel-body">
            <div class="form-group">
                <label class="control-label h6 col-sm-2">عنوان فرم</label>
                <div class="col-sm-10">
                <input type="text" class="form-control width-550 position-left" id="jtitle" maxlength="250" placeholder="عنوان فرم را وارد کنید">
                </div>	
            </div>
            <div class="form-group">
                <label class="control-label h6 col-sm-2">تنظیمات فرم</label>
                <div class="col-sm-10">
                    <div class="row mt-15" >
                        <div class="col-sm-12">
                            <div class="checkbox"><label><input class="icheck" id="jactive" type="checkbox" name="active" value="1" checked>فرم فعال باشد</label></div>
                            <div class="checkbox"><label><input class="icheck" id="jsecurity_code" type="checkbox" name="security_code" value="1" checked>کد امنیتی فعال باشد</label></div>
                            <div class="checkbox"><label><input class="icheck" id="jtracking" type="checkbox" name="tracking" value="1">کد پیگیری فعال باشد</label></div>
                        </div>
                        <div class="col-sm-6" > </div>                        
                    </div>
                </div>	
            </div>

            <div id="fb-editor"></div>
            <div id="fb-rendered-form">
                <form class="jpreview" action="#"></form>
                <button class="btn btn-default edit-form">ویرایش فرم</button>
            </div>
        </div>
        <div class="panel-footer">
        <form method="post" name="addform" id="addform" class="form-horizontal">
            <input type="hidden" name="mod" value="jform">
            <input type="hidden" name="action" value="addform">
            <input type="hidden" id="data" name="data" value="">
            <input type="hidden" id="title" name="title" value="">
            <input type="hidden" id="active" name="active" value="">
            <input type="hidden" id="security_code" name="security_code" value="">
            <input type="hidden" id="tracking" name="tracking" value="">
            <input type="hidden" name="user_hash" value="{$dle_login_hash}">
            <input type="submit" id="sendform" class="btn bg-teal btn-sm btn-raised position-left legitRipple" value="ذخیره فرم">
        </form>
        </div>
    </div>
HTML;
    echo <<<JSCRIPT
    <script>
    var options = {
        i18n: {
          locale: 'fa-IR'
        }
      };
    $(document).ready(function(){
            var jdata,fbEditor = $(document.getElementById('fb-editor')),
              formContainer = $(document.getElementById('fb-rendered-form')),
              fbOptions = {
                disabledSubtypes: {
                    file: ['fineuploader'],
                    textarea: ['tinymce','quill'],
                  },
                roles: {
                    {$groups}
                  },
                typeUserAttrs: {
                    file: {
                        file_types:{
                            label: 'پسوندهای مجاز',
                            value: '',
                            placeholder: 'مانند zip,rar,txt,pdf,rtf,doc,docx',
                        },
                        file_size: {
                            label: 'حداکثر اندازه فایل (KB)',
                            value: 0,
                        }
                    }
                },
                i18n: {
                    locale: 'fa-IR',
                    override: {
                        'fa-IR': {
                            save: 'پیش نمایش',
                            jdate: 'تاریخ شمسی',
                        }
                    }
                  },
                onSave: function() {
                //   console.log(formBuilder.formData);   
                  jdata = formBuilder.formData;               
                  fbEditor.fadeToggle();
                  formContainer.fadeToggle();
                  $('.jpreview', formContainer).formRender({
                    formData: formBuilder.formData
                  });
                }
              },
              formBuilder = fbEditor.formBuilder(fbOptions);
          
            $('.edit-form', formContainer).click(function() {
              fbEditor.fadeToggle();
              formContainer.fadeToggle();
            });
            

        $('#sendform').on('click', function(e){
            var title = $('#jtitle').val();
            var active = ($('#jactive').is(":checked") ? 1:0);
            var security_code = ($('#jsecurity_code').is(":checked") ? 1:0);
            var tracking = ($('#jtracking').is(":checked") ? 1:0);
            var newdata = formBuilder.actions.getData('json');
            $('#data').val(JSON.stringify(newdata));
            $('#title').val(title);
            $('#active').val(active);
            $('#security_code').val(security_code);
            $('#tracking').val(tracking);
            $('#addform').submit();
        });
          
    });
    </script>
JSCRIPT;
    echofooter();
}elseif ($action == 'addform') {
	if (!in_array($member_id['user_group'],$setting['access_group'])){
        msg( "error", $lang['index_denied'], $lang['index_denied'] );
    }
	if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {
		die( "Hacking attempt! User not found" );
    }

    if (empty($_POST['title']) OR count(json_decode($_POST['data'], true)) < 1) {
        $msg = empty($_POST['title']) ? 'لطفا فیلد عنوان فرم را تکمیل نمایید.':'هیچ فیلدی جهت ساخت فرم انتخاب نشده است.';
        msg( "error", 'خطا', $msg, ['?mod=jform&amp;action=new' => 'بازگشت'] );
    }
    
    include_once (DLEPlugins::Check(ENGINE_DIR . '/classes/parse.class.php'));
    $parse = new ParseFilter();

    $title = stripslashes($parse->process(trim(strip_tags($_POST['title']))));
    $data = $db->safesql($_POST['data']);
    $active = isset( $_POST['active'] ) ? intval( $_POST['active'] ) : 0;
    $security_code = isset( $_POST['security_code'] ) ? intval( $_POST['security_code'] ) : 0;
    $tracking = isset( $_POST['tracking'] ) ? intval( $_POST['tracking'] ) : 0;
    $db->query( "INSERT INTO " . PREFIX . "_jform (user_id,title,data,active,security_code,tracking) values ('{$member_id['user_id']}', '$title', '$data', '$active', '$security_code', '$tracking')" );
    msg( "success", 'پیام سیستم', 'فرم با موفقیت افزوده شد.', '?mod=jform' );
} elseif ($action == 'delete') {

    if (!in_array($member_id['user_group'],$setting['access_group'])){
        msg( "error", $lang['index_denied'], $lang['index_denied'] );
    }
    if (!isset($_REQUEST['formid'])){
        msg( "error", 'خطا', 'فرمی جهت حذف کردن انتخاب نشده است!', '?mod=jform' );
    }

    $id = intval($_REQUEST['formid']);
    $folder = ROOT_DIR . "/uploads/files/jform/{$id}";
    if (is_dir( $folder )) {
        // scan files inside directory
        $files = glob($folder . '/*');
        // remove all files inside
        foreach($files as $file) {
            if(is_file($file)) {  
                @unlink($file); 
            } 
        }
        // remove empty directory
        @rmdir($folder);
    }
    // remove form messages
    $db->query( "DELETE FROM " . PREFIX . "_jform_data WHERE form_id='{$id}'" );
    // remove form
    $db->query( "DELETE FROM " . PREFIX . "_jform WHERE id='{$id}'" );

    msg( "success", 'پیام سیستم', 'فرم با موفقیت حذف شد.', '?mod=jform' );

} elseif ($action == 'preview') {
    if (!in_array($member_id['user_group'],$setting['access_group'])){
        msg( "error", $lang['index_denied'], $lang['index_denied'] );
    }
    if (!isset($_REQUEST['id'])){
        msg( "error", 'خطا', 'فرمی جهت پیش نمایش انتخاب نشده است!', '?mod=jform' );
    }
    $id = intval($_REQUEST['id']);

    $form = $db->super_query("SELECT * FROM " . PREFIX . "_jform WHERE id={$id}");
    $js_array[] = 'engine/classes/js/form-render.min.js';
    
    echoheader("<i class=\"fa fa-file-text-o position-left\"></i><span class=\"text-semibold\">پلاگین فرمساز</span>", 'پیش نمایش فرم');
    
    echo <<<HTML
    <div class="panel panel-default">
        <div class="panel-heading">
            پیش نمایش فرم
        </div>
        <div class="panel-body">
            <form method="POST">
                <h4 class="mb-2">{$form['title']}</h4>
                <div class="jpreview"></div>
            </form>
        </div>
        <div class="panel-footer">
            <!-- <input type="submit" class="btn bg-teal btn-sm btn-raised position-left legitRipple" value="ارسال"> -->
        </div>
    </div>
HTML;

echo <<<JSCRIPT
    <script>
    $(document).ready(function(){
        $('.jpreview').formRender({
            formData: {$form['data']}
        });
    });
    </script>
JSCRIPT;

    echofooter();
} elseif ($action == 'editform') {
    if (!in_array($member_id['user_group'],$setting['access_group'])){
        msg( "error", $lang['index_denied'], $lang['index_denied'] );
    }
    if ($member_id['user_group'] != 1){
        msg( "error", $lang['index_denied'], $lang['index_denied'] );
    }
    if (!isset($_REQUEST['formid'])){
        msg( "error", 'خطا', 'فرمی جهت ویرایش انتخاب نشده است!', '?mod=jform' );
    }
    $id = intval($_REQUEST['formid']);
    $form = $db->super_query("SELECT * FROM " . PREFIX . "_jform WHERE id={$id}");
    $factive = $form['active'] == 1 ? 'checked':'';
    $fsecurity_code = $form['security_code'] == 1 ? 'checked':'';
    $ftracking = $form['tracking'] == 1 ? 'checked':'';
    $js_array[] = 'engine/classes/js/jquery-ui.min.js';
    $js_array[] = 'engine/classes/js/form-builder.min.js';
    $js_array[] = 'engine/classes/js/form-render.min.js';
    echoheader("<i class=\"fa fa-file-text-o position-left\"></i><span class=\"text-semibold\">پلاگین فرمساز</span>", 'افزودن فرم جدید');
    $groups = '';
    foreach($user_group as $item){
        $groups .= $item['id'] . ':' . '"' . $item['group_name'] . '"' . ',';
    }
    jheader();
    echo <<<HTML
    <style>
        #fb-rendered-form {
        clear:both;
        display:none;
        }
    </style>
    <div class="panel panel-default">
        <div class="panel-heading">
        افزودن فرم
            <!-- <div class="heading-elements not-collapsible">
                <ul class="icons-list">
                    <li><a href="#" data-toggle="modal" data-target="#advancedadd"><i class="fa fa-user-plus position-left"></i><span class="visible-lg-inline visible-md-inline visible-sm-inline">اضافه کردن کاربر </span></a></li>
                </ul>
            </div> -->
        </div>
        <div class="panel-body">
            <div class="form-group">
                <label class="control-label h6 col-sm-2">عنوان فرم</label>
                <div class="col-sm-10">
                <input type="text" class="form-control width-550 position-left" id="jtitle" maxlength="250" value="{$form['title']}">
                </div>	
            </div>
            <div class="form-group">
                <label class="control-label h6 col-sm-2">تنظیمات فرم</label>
                <div class="col-sm-10">
                    <div class="row mt-15" id="opt_cat_rss">
                        <div class="col-sm-6" style="max-width:300px;">
                            <div class="checkbox"><label><input class="icheck" id="jactive" type="checkbox" name="allow_rss" value="1" {$factive}>فرم فعال باشد</label></div>
                            <div class="checkbox"><label><input class="icheck" id="jsecurity_code" type="checkbox" name="security_code" value="1" {$fsecurity_code}>کد امنیتی فعال باشد</label></div>
                            <div class="checkbox"><label><input class="icheck" id="jtracking" type="checkbox" name="tracking" value="1" {$ftracking}>کد پیگیری فعال باشد</label></div>
                        </div>
                    </div>
                </div>	
            </div>

            <div id="fb-editor"></div>
            <div id="fb-rendered-form">
                <form class="jpreview" action="#"></form>
                <button class="btn btn-default edit-form">ویرایش فرم</button>
            </div>
        </div>
        <div class="panel-footer">
        <form style="display:none;" method="post" name="doedit" id="doedit" class="form-horizontal">
            <input type="hidden" name="mod" value="jform">
            <input type="hidden" name="action" value="doedit">
            <input type="hidden" id="id" name="id" value="{$form['id']}">
            <input type="hidden" id="data" name="data" value="">
            <input type="hidden" id="title" name="title" value="">
            <input type="hidden" id="active" name="active" value="">
            <input type="hidden" id="security_code" name="security_code" value="">
            <input type="hidden" id="tracking" name="tracking" value="">
            <input type="hidden" name="user_hash" value="{$dle_login_hash}">
        </form>
        <a href="#" id="sendform" class="btn bg-teal btn-sm btn-raised position-left legitRipple">ویرایش فرم</a>
        <a href="?mod=jform" class="btn bg-danger btn-sm btn-raised legitRipple">بازگشت</a>
        </div>
    </div>
HTML;
    echo <<<JSCRIPT
    <script>
    var options = {
        i18n: {
          locale: 'fa-IR'
        }
      };
    $(document).ready(function(){
        var formData = {$form['data']};
            var jdata,fbEditor = $(document.getElementById('fb-editor')),
              formContainer = $(document.getElementById('fb-rendered-form')),
              fbOptions = {
                formData: formData,
                disabledSubtypes: {
                    file: ['fineuploader'],
                    textarea: ['tinymce','quill'],
                  },
                roles: {
                    {$groups}
                  },
                typeUserAttrs: {
                    file: {
                        file_types:{
                            label: 'پسوندهای مجاز',
                            value: '',
                            placeholder: 'مانند zip,rar,txt,pdf,rtf,doc,docx',
                        },
                        file_size: {
                            label: 'حداکثر اندازه فایل (KB)',
                            value: 0
                        }
                    }
                },
                i18n: {
                    locale: 'fa-IR',
                    override: {
                        'fa-IR': {
                            save: 'پیش نمایش',
                            jdate: 'تاریخ شمسی',
                        }
                    }
                  },
                onSave: function() {
                //   console.log(formBuilder.formData);   
                  jdata = formBuilder.formData;               
                  fbEditor.fadeToggle();
                  formContainer.fadeToggle();
                  $('.jpreview', formContainer).formRender({
                    formData: formBuilder.formData
                  });
                }
              },
              formBuilder = fbEditor.formBuilder(fbOptions);
            $('.edit-form', formContainer).click(function() {
              fbEditor.fadeToggle();
              formContainer.fadeToggle();
            });
            

        $('#sendform').on('click', function(e){
            e.preventDefault();
            var title = $('#jtitle').val();
            var active = ($('#jactive').is(":checked") ? 1:0);
            var security_code = ($('#jsecurity_code').is(":checked") ? 1:0);
            var tracking = ($('#jtracking').is(":checked") ? 1:0);
            var newdata = formBuilder.actions.getData('json');
            $('#data').val(JSON.stringify(newdata));
            $('#title').val(title);
            $('#active').val(active);
            $('#security_code').val(security_code);
            $('#tracking').val(tracking);
            $('#doedit').submit();
        });
        
        // extract size and type for file fields
        var t = JSON.parse(formData);
        var all_sizes = [];
        var all_types = [];
        t.forEach(function(item){
            if (item.file_size){
                all_sizes.push ({
                    'name' : item.name,
                    'size' : item.file_size
                });
            }
            if (item.file_types){
                all_types.push({
                    'name' : item.name,
                    'type' : item.file_types
                });
            }
        });

        // wait for form editor to load - then apply file fields size and type from database (if there is file field inside form!)
        $(document).on('loaded', function(){
            if (all_sizes.length > 0) {
                all_sizes.forEach(function(item){
                    var a = $('input').filter(function() { return this.value == item.name });
                    var b = $('input[name="file_size"]');
                    var c = a.parent().parent().parent().find(b);
                    c.val(parseInt(item.size));
                })
            }
            if (all_types.length > 0) {
                all_types.forEach(function(item){
                    var a = $('input').filter(function() { return this.value == item.name });
                    var b = $('input[name="file_types"]');
                    var c = a.parent().parent().parent().find(b);
                    c.val(item.type);
                })
            }
        });        
          
    });
    </script>
JSCRIPT;
    echofooter();
} elseif ($action ==  'doedit') {
    if (!in_array($member_id['user_group'],$setting['access_group'])){
        msg( "error", $lang['index_denied'], $lang['index_denied'] );
    }
    @header('X-XSS-Protection: 0;');
	
	if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {
		die( "Hacking attempt! User not found" );
    }

    if (!isset($_REQUEST['id'])){
        msg( "error", 'خطا', 'فرمی جهت ویرایش انتخاب نشده است!', '?mod=jform' );
    }
    $id = intval($_REQUEST['id']);

    if (empty($_POST['title']) OR count(json_decode($_POST['data'], true)) < 1) {
        $msg = empty($_POST['title']) ? 'لطفا فیلد عنوان فرم را تکمیل نمایید.':'هیچ فیلدی جهت ساخت فرم انتخاب نشده است.';
        msg( "error", 'خطا', $msg, ['?mod=jform&amp;action=new' => 'بازگشت'] );
    }

    include_once (DLEPlugins::Check(ENGINE_DIR . '/classes/parse.class.php'));
    $parse = new ParseFilter();

    $title = stripslashes($parse->process(trim(strip_tags($_POST['title']))));
    $data = $db->safesql($_POST['data']);
    $active = isset( $_POST['active'] ) ? intval( $_POST['active'] ) : 0;
    $security_code = isset( $_POST['security_code'] ) ? intval( $_POST['security_code'] ) : 0;
    $tracking = isset( $_POST['tracking'] ) ? intval( $_POST['tracking'] ) : 0;

    $db->query( "UPDATE " . PREFIX . "_jform SET title='{$title}', active='{$active}', data='{$data}', security_code='{$security_code}', tracking={$tracking}   WHERE id='{$id}'" );
    
    msg( "success", 'پیام سیستم', 'فرم با موفقیت ویرایش شد.', '?mod=jform' );

} elseif ($action == 'msg_short') {
    if (!in_array($member_id['user_group'],$setting['data_access_group'])){
        msg( "error", $lang['index_denied'], $lang['index_denied'] );
    }
    $prev = (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'javascript:history.go(-1)');
    if (!isset($_REQUEST['formid'])){
        msg( "error", 'خطا', 'فرمی جهت نمایش انتخاب نشده است!', $prev );
    }
    $id = intval($_REQUEST['formid']);
    $db->query("SELECT jd.id,jd.user_id,jd.form_id,jd.msg_read,jd.tracking,jd.description,jd.date,u.name FROM (SELECT id,user_id,form_id,msg_read,tracking,description,date FROM " . PREFIX . "_jform_data WHERE form_id={$id}) jd 
    LEFT JOIN " . PREFIX . "_users u ON (jd.user_id=u.user_id) ORDER BY date ASC");
    $items = "";
    $i = 1;
    while ( $row = $db->get_array() ) {
        $row['date'] = jdate('Y/m/d - H:i:s', strtotime( $row['date']));
        $row['name'] = ( $row['name'] == '' ? 'میهمان' :  $row['name'] );
        $row['msg_read'] = ( $row['msg_read'] == 1 ? '<span class="text-success"><i class="fa fa-check-circle"></i></span> خوانده شده' :  '<span class="text-danger"><i class="fa fa-exclamation-circle"></i></span> خوانده نشده' );
        $row['description'] = empty($row['description']) ? '':substr(strip_tags($row['description']), 0, 40) . '...';

        $items .="<tr>
        <td class=\"text-center\">{$i}</td>
        <td class=\"text-center\">{$row['name']}</td>
        <td class=\"text-center\">{$row['msg_read']}</td>
        <td class=\"text-center\">{$row['tracking']}</td>
        <td class=\"text-center\">{$row['description']}</td>
        <td class=\"text-center\">{$row['date']}</td>
        <td class=\"text-center\">
            <a href=\"{$PHP_SELF}?mod=jform&amp;action=viewmsg&amp;id={$row['id']}\" target=\"_blank\" class=\"tip text-success\" data-original-title=\"مشاهده پیام\"><i class=\"fa fa-envelope-open-o\"></i></a>&nbsp;&nbsp;
            <a href=\"#\" onclick=\"javascript:Remove('{$row['id']}'); return false;\" class=\"tip text-danger\" data-original-title=\"حذف پیام\"><i class=\"fa fa-trash\"></i></a>&nbsp;&nbsp;
        </td>
        </tr>";
        $i++;
    }
    $db->free();

    $js_array[] = 'engine/skins/javascripts/jquery.dataTables.js';
    $js_array[] = 'engine/skins/javascripts/dataTables.bootstrap.min.js';
    $js_array[] = 'engine/skins/javascripts/dataTables.buttons.min.js';
    $js_array[] = 'engine/skins/javascripts/jszip.min.js';
    $js_array[] = 'engine/skins/javascripts/buttons.html5.min.js';
    $js_array[] = 'engine/skins/javascripts/buttons.colVis.min.js';
    $css_array[] = 'engine/skins/stylesheets/dataTables.bootstrap4.min.css';
    $css_array[] = 'engine/skins/stylesheets/buttons.dataTables.min.css';

    echoheader("<i class=\"fa fa-file-text-o position-left\"></i><span class=\"text-semibold\">پلاگین فرمساز</span>", 'اطلاعات فرم');
    jheader();
    echo <<<HTML
    <div class="panel panel-default">
        <div class="panel-heading">
            لیست پیام‌ها
            <div class="heading-elements not-collapsible">
                <ul class="icons-list">
                    <li><a href="?mod=jform&amp;action=msg_full&amp;formid={$id}"><i class="fa fa-list position-left"></i><span class="visible-lg-inline visible-md-inline visible-sm-inline">لیست همه پاسخ های ارسال شده</span></a></li>
                </ul>
            </div>
        </div>
        <div class="panel-body">
        <table id="jlist" class="table table-striped table-xs table-hover">
        <thead>
            <tr>
                <th class="text-center hidden-xs hidden-sm" style="width: 60px;">شماره</th>
                <th class="text-center" style="width: 160px;">ارسال کننده</th>
                <th class="text-center" style="width: 160px;">وضعیت</th>
                <th class="text-center">کد پیگیری</th>
                <th class="text-center">توضیحات</th>
                <th class="text-center" style="width: 160px;">تاریخ ارسال</th>
                <th class="text-center" style="width: 180px;">
                    عملیات
                </th>
            </tr>
        </thead>
            <tbody>
                {$items}
            </tbody>
        </table>
        </div>
        <div class="panel-footer">
            <a href="?mod=jform" class="btn bg-danger btn-sm btn-raised position-left legitRipple">بازگشت</a>
        </div>
HTML;
echo <<<JSCRIPT
    <script>
    $(document).ready( function () {
        $('#jlist').DataTable({
            "language": {
                "url": "https://cdn.datatables.net/plug-ins/1.10.20/i18n/Persian.json"
            },
            dom: 'Bfrtip',
            buttons: [
                {
                    extend: 'copyHtml5',
                    text: 'کپی',
                },
                {
                    extend: 'excelHtml5',
                    text: 'خروجی اکسل',
                    title: 'لیست پیام‌های {$form['title']}',
                    exportOptions: {
                        columns: ':visible:not(.not-export-col)'
                    }
                },
                {
                    extend: 'csvHtml5',
                    text: 'خروجی csv',
                    title: 'لیست پیام‌های {$form['title']}',
                    exportOptions: {
                        columns: ':visible:not(.not-export-col)'
                    }
                },
                {
                    extend: 'colvis',
                    text: 'تنظیمات نمایش',
                }
            ]
        });
    });
    $('#jlist').one('draw.dt', function () {
        $('#jlist_paginate').wrap('<div class="col-sm-7"></div>').css('text-align', 'left');
        $('#jlist_info').wrap('<div class="col-sm-5"></div>');
    });
    function Remove( id ){
        DLEconfirm('آیا از حذف این فرم مطمئن هستید؟', 'پیام', function(){
          window.location = "{$PHP_SELF}?mod=jform&action=delmsg&id=" + id;
        });
        return false;
    }
    </script>
JSCRIPT;
    echofooter();


} elseif ($action == 'msg_full') {
    if (!in_array($member_id['user_group'],$setting['data_access_group'])){
        msg( "error", $lang['index_denied'], $lang['index_denied'] );
    }
    $prev = (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'javascript:history.go(-1)');
    if (!isset($_REQUEST['formid'])){
        msg( "error", 'خطا', 'فرمی جهت نمایش انتخاب نشده است!', $prev );
    }
    $id = intval($_REQUEST['formid']);
    $form = $db->super_query("SELECT * FROM " . PREFIX . "_jform WHERE id='{$id}' ");
    $data = json_decode(json_decode($form['data']));
    
    // get fields which have name (not fields like paragraph or header)
    foreach($data as $item) {
        if (isset($item->name) ){
            $form_fields[] =  $item;
        }
    }
    $header_titles = "";
    // set dynamic table headers
    foreach ($form_fields as $field) {
        $header_titles .= "<th>{$field->label}</th>";
    }

    $db->query("SELECT * FROM " . PREFIX . "_jform_data WHERE form_id={$id} ORDER BY date DESC");
    $items = "";

    while ( $row = $db->get_array() ) {
        $row['date'] = jdate('Y/m/d - H:i:s', strtotime( $row['date']));
        // transform string(json) data to array for handling data
        $form_data = json_decode($row['form_data'], true);
        
        $dynamic_data = "";
        // get dynamic fields values using field name attribute
        foreach ($form_fields as $field){
            $dynamic_data .= "<td>{$form_data["$field->name"]}</td>";
        }
        
        $items .="<tr>
        <td>{$row['id']}</td>
        {$dynamic_data}
        <td class=\"text-center\">{$row['date']}</td>
        <td class=\"text-center\">
            <a href=\"{$PHP_SELF}?mod=jform&amp;action=viewmsg&amp;id={$row['id']}\" target=\"_blank\" class=\"tip text-success\" data-original-title=\"مشاهده پیام\"><i class=\"fa fa-envelope-open-o\"></i></a>&nbsp;&nbsp;
            <a href=\"#\" onclick=\"javascript:Remove('{$row['id']}'); return false;\" class=\"tip text-danger\" data-original-title=\"حذف پیام\"><i class=\"fa fa-trash\"></i></a>&nbsp;&nbsp;
        </td>
        </tr>";
    }
    $db->free();

    $js_array[] = 'engine/skins/javascripts/jquery.dataTables.js';
    $js_array[] = 'engine/skins/javascripts/dataTables.bootstrap.min.js';
    $js_array[] = 'engine/skins/javascripts/dataTables.buttons.min.js';
    $js_array[] = 'engine/skins/javascripts/jszip.min.js';
    $js_array[] = 'engine/skins/javascripts/buttons.html5.min.js';
    $js_array[] = 'engine/skins/javascripts/buttons.colVis.min.js';
    $css_array[] = 'engine/skins/stylesheets/dataTables.bootstrap4.min.css';
    $css_array[] = 'engine/skins/stylesheets/buttons.dataTables.min.css';

    echoheader("<i class=\"fa fa-file-text-o position-left\"></i><span class=\"text-semibold\">پلاگین فرمساز</span>", 'اطلاعات فرم');
    jheader();
    echo <<<HTML
    <div class="panel panel-default">
        <div class="panel-heading">
            لیست پیام‌های {$form['title']}
        </div>
        <div class="panel-body">
        <table id="jlist" class="table table-striped table-xs table-hover">
        <thead>
            <tr>
                <th class="hidden-xs hidden-sm" style="width: 60px;">شماره</th>
                {$header_titles}
                <th class="text-center" style="width: 160px;">تاریخ ارسال</th>
                <th class="text-center not-export-col" style="width: 180px;">
                    عملیات
                </th>
            </tr>
        </thead>
            <tbody>
                {$items}
            </tbody>
        </table>
        </div>
        <div class="panel-footer">
            <a href="{$prev}" class="btn bg-danger btn-sm btn-raised position-left legitRipple">بازگشت</a>
        </div>
HTML;
echo <<<JSCRIPT
    <script>
    $(document).ready( function () {
        $('#jlist').DataTable({
            "language": {
                "url": "https://cdn.datatables.net/plug-ins/1.10.20/i18n/Persian.json"
            },
            dom: 'Bfrtip',
            buttons: [
                {
                    extend: 'copyHtml5',
                    text: 'کپی',
                },
                {
                    extend: 'excelHtml5',
                    text: 'خروجی اکسل',
                    title: 'لیست پیام‌های {$form['title']}',
                    exportOptions: {
                        columns: ':visible:not(.not-export-col)'
                    }
                },
                {
                    extend: 'csvHtml5',
                    text: 'خروجی csv',
                    title: 'لیست پیام‌های {$form['title']}',
                    exportOptions: {
                        columns: ':visible:not(.not-export-col)'
                    }
                },
                {
                    extend: 'colvis',
                    text: 'تنظیمات نمایش',
                }
            ]
        });        
    });
    $('#jlist').one('draw.dt', function () {
        $('#jlist_paginate').wrap('<div class="col-sm-7"></div>').css('text-align', 'left');
        $('#jlist_info').wrap('<div class="col-sm-5"></div>');
    });
    function Remove( id ){
        DLEconfirm('آیا از حذف این فرم مطمئن هستید؟', 'پیام', function(){
          window.location = "{$PHP_SELF}?mod=jform&action=delmsg&id=" + id;
        });
        return false;
    }
    </script>
JSCRIPT;
    echofooter();
} elseif ($action == 'viewmsg') {
    if (!in_array($member_id['user_group'],$setting['data_access_group'])){
        msg( "error", $lang['index_denied'], $lang['index_denied'] );
    }
    if (!isset($_REQUEST['id'])){
        msg( "error", 'خطا', 'پیامی جهت نمایش انتخاب نشده است!', '?mod=jform' );
    }
    $id = intval($_REQUEST['id']);
    $db->query("UPDATE " . PREFIX . "_jform_data SET msg_read=1 WHERE id='{$id}'");
    $all_data = $db->super_query("
    SELECT jd.*,j.title,j.data,j.tracking,u.name FROM (SELECT * FROM " . PREFIX . "_jform_data jd WHERE id={$id}) jd LEFT JOIN " 
    . PREFIX . "_jform j ON (jd.form_id=j.id) LEFT JOIN " . PREFIX . "_users u ON (jd.user_id=u.user_id)"
    );
    
    $js_array[] = "engine/editor/jscripts/tiny_mce/tinymce.min.js";
    $data = json_decode(json_decode($all_data['data']));
    $form_data = json_decode($all_data['form_data'], true);
    // get fields which have name (not fields like paragraph or header)
    foreach($data as $item) {
        if (isset($item->name) ){
            $form_fields[] =  $item;
        }
    }
    $items = "";
    // set dynamic table headers and data
    foreach ($form_fields as $field) {
        if ($field->type != 'file'){
            $form_data[$field->name] = htmlentities($form_data[$field->name]);
        }
        switch ($field->type) {
            case 'select':
                if (isset($field->multiple)) {
                    $show = "<select id=\"groups_view\" class=\"uniform\" multiple>";
                    foreach ($field->values as $value) {
                        $selected =  $value->value == $form_data[$field->name] ? 'selected': 'disabled';
                        $selected =  in_array($value->value, explode('||', $form_data[$field->name])) ? 'selected': '';
                        $show .= "<option value=\"{$value->value}\" {$selected}>{$value->label}</option>";
                    }
                    $show .= "</select>";
                } else {
                    $show = "<select class=\"uniform\" name=\"\" id=\"\">";
                    foreach ($field->values as $value) {
                        $selected =  $value->value == $form_data[$field->name] ? 'selected': 'disabled';
                        $show .= "<option value=\"{$value->value}\" {$selected}>{$value->label}</option>";
                    }
                    $show .= "</select>";
                }
                break;
            case 'checkbox-group':
                $show = "";
                foreach ($field->values as $value) {
                    $checked =  in_array($value->value, explode('||', $form_data[$field->name])) ? 'checked': 'disabled';
                    $show .= "<div class=\"checkbox\"><label><input value=\"{$value->value}\" class=\"icheck\" type=\"checkbox\" name=\"\" $checked />{$value->label}</label></div>";
                }
                break;
            case 'file':
                if ($form_data[$field->name]){                
                    if (isset($field->multiple)) {
                        $show = "";
                        $files = explode(',', $form_data[$field->name]);
                        $i = 1;
                        foreach ($files as $file) {
                            $show .= "<a class=\"btn btn-info mx-5\" target=\"_blank\" href=\"/uploads/files/jform/{$all_data['form_id']}/{$file}\">دانلود فایل {$i}</a> &nbsp;&nbsp;";
                            $i++;
                        }
                    } else {
                        $show = "<a class=\"btn btn-info\" target=\"_blank\" href=\"/uploads/files/jform/{$all_data['form_id']}/{$form_data[$field->name]}\">دانلود فایل</a>";
                    }
                } else {
                    $show = "فایلی ارسال نشده است.";
                }
                break;
            case 'textarea':
                $show = "<textarea class=\"classic\" style=\"width:100%;height:100px;\" name=\"\" readonly>{$form_data[$field->name]}</textarea>";
                break;
            case 'radio-group':
                $show = "";
                foreach ($field->values as $value) {
                    $checked =  $value->value == $form_data[$field->name] ? 'checked': 'disabled';
                    $show .= "<label class=\"radio-inline\"><input class=\"icheck\" type=\"radio\" id=\"\" name=\"\" value=\"{$value->value}\" $checked>{$value->label}</label>";
                }
                break;
            case 'autocomplete':
                $show = "<select class=\"uniform\" name=\"\" id=\"\">";
                    foreach ($field->values as $value) {
                        $selected =  $value->value == $form_data[$field->name] ? 'selected': 'disabled';
                        $show .= "<option value=\"{$value->value}\" {$selected}>{$value->label}</option>";
                    }
                $show .= "</select>";
                break;
            
            default:
                $show = "<input type=\"text\" class=\"form-control\" value=\"{$form_data[$field->name]}\" readonly>";
                break;
        }

        $items .= " <tr>
						<td class=\"col-xs-6 col-sm-6 col-md-6\">
							<h6 class=\"media-heading text-semibold\">{$field->label}</h6>
							<span class=\"text-muted text-size-small hidden-xs\"></span>
						</td>
                        <td class=\"col-xs-6 col-sm-6 col-md-6\">
                         {$show}
                        </td>
					</tr>";
    }
    echoheader("<i class=\"fa fa-file-text-o position-left\"></i><span class=\"text-semibold\">پلاگین فرمساز</span>", 'اطلاعات پیام');

    jheader();
    echo <<<HTML
    <div class="panel panel-default">
        <div class="panel-heading">
			اطلاعات پیام شماره {$all_data['id']} از {$all_data['title']}
        </div>
        <div class="table-responsive">
            <table class="table table-striped">
                <tbody>
                    {$items}
                </tbody>
            </table>
        </div>
    </div>
HTML;

    if ($all_data['tracking'] == 1) {
        $ed_root = explode ( pathinfo($_SERVER['PHP_SELF'], PATHINFO_BASENAME), $_SERVER['PHP_SELF'] );
	    $ed_root = reset ( $ed_root );
    echo <<<HTML
    <script>
jQuery(function($){

	tinyMCE.baseURL = '{$ed_root}engine/editor/jscripts/tiny_mce';
	tinyMCE.suffix = '.min';

	if(dle_theme === null) dle_theme = '';

	tinymce.init({
		selector: 'textarea.wysiwygeditor',
		language : "{$lang['wysiwyg_language']}",
		element_format : 'html',
		body_class: dle_theme,
		width : "100%",
		height : 310,
		plugins: ["fullscreen advlist autolink lists link image charmap anchor searchreplace visualblocks visualchars media nonbreaking table contextmenu emoticons paste textcolor colorpicker codemirror spellchecker dlebutton codesample hr"],
		relative_urls : false,
		convert_urls : false,
		remove_script_host : false,
		toolbar_items_size: 'small',
		verify_html: false,
		branding: false,
		menubar: false,
		image_advtab: true,
		image_dimensions: false,
		image_caption: true,
		toolbar1: "formatselect fontselect fontsizeselect | link anchor dleleech unlink | dleemo dlemp dletube dlaudio | dlehide dlequote dlespoiler codesample hr visualblocks dlebreak dlepage code",
		toolbar2: "undo redo | copy paste pastetext | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | subscript superscript | table bullist numlist | forecolor backcolor | spellchecker dletypo removeformat searchreplace fullscreen",
		formats: {
		  bold: {inline: 'b'},  
		  italic: {inline: 'i'},
		  underline: {inline: 'u', exact : true},  
		  strikethrough: {inline: 's', exact : true}
		},
		codesample_languages: [ {text: 'HTML/JS/CSS', value: 'markup'}],
		spellchecker_language : "ru",
		spellchecker_languages : "Russian=ru,Ukrainian=uk,English=en",
		spellchecker_rpc_url : "https://speller.yandex.net/services/tinyspell",
		content_css : "engine/editor/css/content.css"

	});

});
</script>
    <form method="POST">
        <div class="panel panel-default mt-2">
            <div class="panel-heading">
                ارسال توضیحات به ارسال کننده فرم
            </div>
            <div class="panel-body">
                <textarea id="description" name="description" class="wysiwygeditor" style="width:98%;height:300px;">{$all_data['description']}</textarea>
            </div>
            <div class="panel-footer">
                <input type="hidden" name="mod" value="jform">
                <input type="hidden" name="action" value="add_description">
                <input type="hidden" name="id" value="{$all_data['id']}">
                <input type="hidden" name="user_hash" value="{$dle_login_hash}">
                <input type="submit" id="sendform" class="btn bg-teal btn-sm btn-raised position-left legitRipple" value="ارسال">
            </div>
        </div>
    </form>
HTML;
}
    echofooter();

} elseif ($action == 'add_description') {
    if (!in_array($member_id['user_group'],$setting['data_access_group'])){
        msg( "error", $lang['index_denied'], $lang['index_denied'] );
    }
    if( $_POST['user_hash'] == "" or $_POST['user_hash'] != $dle_login_hash ) {
		die( "Hacking attempt! User not found" );
    }
    if (!isset($_POST['id'])){
        msg( "error", 'خطا', 'پیامی جهت افزودن توضیحات انتخاب نشده است!', 'javascript:history.go(-1)' );
    }
    
    $prev = (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'javascript:history.go(-1)');

    include_once (DLEPlugins::Check(ENGINE_DIR . '/classes/parse.class.php'));
    $parse = new ParseFilter();

    $id = intval($_POST['id']);
    $description = $parse->process($_POST['description']);
    $db->query("UPDATE " . PREFIX . "_jform_data SET description='{$description}' WHERE id={$id}");
    msg( "success", 'پیام سیستم', 'توضیحات با موفقیت افزوده شد', $prev );

} elseif ($action == 'delmsg') {
    if (!in_array($member_id['user_group'],$setting['data_access_group'])){
        msg( "error", $lang['index_denied'], $lang['index_denied'] );
    }
    if (!isset($_REQUEST['id'])){
        msg( "error", 'خطا', 'پیامی جهت حذف انتخاب نشده است!', 'javascript:history.go(-1)' );
    }

    $id = intval($_REQUEST['id']);
    
    
    /**
     * if message has files
     * files should be removed
     */

    $form = $db->super_query("SELECT j.id,j.data,jd.form_data FROM (SELECT form_id,form_data FROM " . PREFIX . "_jform_data WHERE id={$id}) jd 
    LEFT JOIN " . PREFIX . "_jform j ON (jd.form_id=j.id)");
    $fields = json_decode(json_decode($form['data']));
    $data = json_decode($form['form_data'], true);
    
    foreach ($fields as $field) {
        if ($field->type == 'file') {
            if ($data["$field->name"]) {
                $files = explode(',', $data["$field->name"]);
            }
        }
    }
    if (count($files) > 0) {
        foreach ($files as $file) {
            @unlink( ROOT_DIR . "/uploads/files/jform/{$form['id']}/{$file}"  );
        }
    }


    $db->query( "DELETE FROM " . PREFIX . "_jform_data WHERE id='{$id}'" );
    $prev = (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'javascript:history.go(-1)');
    msg( "success", 'پیام سیستم', 'پیام با موفقیت حذف شد.', $prev );
} elseif ($action == 'setting') {
    if ($member_id['user_group'] != 1){
        msg( "error", $lang['index_denied'], $lang['index_denied'] );
    }
    $access_groups = "";
    $data_access_groups = "";
    foreach($user_group as $item){
        $selected = (in_array($item['id'], $setting['access_group']) ? 'selected':'');
        $selected_data = (in_array($item['id'], $setting['data_access_group']) ? 'selected':'');
        $access_groups .= "<option value=\"{$item['id']}\" {$selected}>{$item['group_name']}</option>";
        $data_access_groups .= "<option value=\"{$item['id']}\" {$selected_data}>{$item['group_name']}</option>";
    }
    $seo = $setting['seo'] ? 'checked':'';
    echoheader("<i class=\"fa fa-file-text-o position-left\"></i><span class=\"text-semibold\">پلاگین فرمساز</span>", 'تنظیمات پلاگین');
    jheader();
    echo <<<HTML
    <div class="panel panel-default">
        <form method="POST">
            <div class="panel-body">
                تنظیمات پلاگین
            </div>
            <div class="table-responsive">
                <table class="table table-striped">
                    <tbody>
                    <tr>
                        <td class="col-xs-6 col-sm-6 col-md-7">
                            <h6 class="media-heading text-semibold">دسترسی به پلاگین:</h6>
                            <span class="text-muted text-size-small hidden-xs">انتخاب گروه‌های کاربری که به قسمت‌های افزودن، ویرایش و حذف فرم دسترسی دارند.</span>
                        </td>
                        <td class="col-xs-6 col-sm-6 col-md-5">
                            <div class="btn-group bootstrap-select uniform">
                                <select data-placeholder="لطفا گرو‌های کاربری را انتخاب کنید" class="uniform" name="access_groups[]" id="access_groups" multiple>
                                    <option value="0" disabled="">لطفا گروه های کاربری مورد نظر خود را انتخاب نمایید.</option>
                                    {$access_groups}
                                </select>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="col-xs-6 col-sm-6 col-md-7">
                            <h6 class="media-heading text-semibold">دسترسی به پیام‌ها:</h6>
                            <span class="text-muted text-size-small hidden-xs">انتخاب گروه‌های کاربری که به قسمت‌های لیست، ویرایش و حذف پیام‌ها دسترسی دارند.</span>
                        </td>
                        <td class="col-xs-6 col-sm-6 col-md-5">
                            <div class="btn-group bootstrap-select uniform">
                                <select data-placeholder="لطفا گرو‌های کاربری را انتخاب کنید" class="uniform" name="data_access_groups[]" id="data_access_groups" multiple>
                                    <option value="0" disabled="">لطفا گروه های کاربری مورد نظر خود را انتخاب نمایید.</option>
                                    {$data_access_groups}
                                </select>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="col-xs-6 col-sm-6 col-md-7">
                            <h6 class="media-heading text-semibold">فعال بودن سئوی آدرس فرم‌ها:</h6>
                            <span class="text-muted text-size-small hidden-xs">اگر فعال باشد، آدرس ها بصورت http://yoursite.com/jform/1-عنوان-فرم.html و در حالت غیر فعال، آدرس ها بصورت http://yoursite.com/index.php?do=jform&formid=1 تعریف خواهند شد.</span>
                        </td>
                        <td class="col-xs-6 col-sm-6 col-md-5">
                            <input class="switch" type="checkbox" name="seo" value="1" {$seo}>
                        </td>
                    </tr>
                    </tbody>
                </table>            
            </div>
            <div class="panel-footer">
                <input type="hidden" name="mod" value="jform">
                <input type="hidden" name="action" value="dosetting">
                <input type="hidden" name="user_hash" value="{$dle_login_hash}">
                <button type="submit" class="btn bg-success btn-sm btn-raised position-left legitRipple">ذخیره</button>
            </div>
        </form>
    </div>
HTML;
    copyright();
    echofooter();
    
} elseif ($action == 'dosetting') {
    if ($member_id['user_group'] != 1){
        msg( "error", $lang['index_denied'], $lang['index_denied'] );
    }
    if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {
		die( "Hacking attempt! User not found" );
    }
    $prev = (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'javascript:history.go(-1)');
    $access_groups = $_POST['access_groups'];
    $data_access_groups = $_POST['data_access_groups'];
    $seo = isset($_POST['seo']) ? intval($_POST['seo']):0;
    if (!is_array($access_groups) OR !is_array($data_access_groups)) {
        msg( "error", 'خطا', 'تنظیمات معتبر نیست', $prev );
    }
    if (count($access_groups) < 1 OR count($data_access_groups) < 1) {
        msg( "error", 'خطا', 'لطفا حداقل یک گروه کاربری را انتخاب کنید', $prev );
    }

    $new_setting = [
        'access_group' => $access_groups,
        'data_access_group' => $data_access_groups,
        'seo' => $seo
    ];

    //save new setting
    file_put_contents( SETTING, serialize($new_setting) );

    msg( "success", 'پیام سیستم', 'تنظیمات با موفقیت ذخیره شد.', $prev );
}