<?php
/*
=====================================================
DataLife Engine v14.0
-----------------------------------------------------
Jform Plugin By Jamal Yarali
-----------------------------------------------------
https://github.com/jyarali/jform-dle
=====================================================
 */

if (!defined('DATALIFEENGINE')) {
    header("HTTP/1.1 403 Forbidden");
    header('Location: ../../');
    die("Hacking attempt!");
}

if (isset($_REQUEST['mod']) AND $_REQUEST['mod'] = 'track') {
    if (isset($_POST['code'])) {
        if ($_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash) {
            msgbox('خطا', $lang['sess_error']);
            return false;
        }
        $code = $db->safesql(stripslashes(trim(strip_tags($_POST['code']))));
        if (!preg_match ("/^[a-z0-9]+$/", $code) OR strlen($code) != 20){
            msgbox('خطا', 'کد پیگیری نامعتبر است.');
            return false;
        }
        $description = $db->super_query("SELECT description,tracking FROM " . PREFIX . "_jform_data WHERE tracking='{$code}' ");
        if ($description) {
            if ($description['description'] == ''){
                $description['description'] = 'پیامی برای شما ارسال نشده است.';
            }
            $tpl->result['content'] = $description['description'];
        } else{
            msgbox('خطا', 'کد رهگیری یافت نشد.');
        }
    } else {
        $tpl->result['content'] = "
        <form  method=\"POST\" id=\"tracking\" name=\"tracking\" action=\"\">\n
        <div class=\"form-group\">
            <input class=\"form-control\" placeholder=\"کد پیگیری\" title=\"کد پیگیری\" type=\"text\" name=\"code\"  required>
        </div>
        <input name=\"do\" type=\"hidden\" value=\"jform\" />
        <input name=\"mod\" type=\"hidden\" value=\"track\" />
        <input name=\"user_hash\" type=\"hidden\" value=\"{$dle_login_hash}\" />
        <div class=\"form-group\">
            <button class=\"btn btn-big\" type=\"submit\" name=\"send_btn\"><b>ارسال</b></button>
        </div>
        </form>";
    }
}
elseif (isset($_POST['send'])) {
    if ($_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash) {
        $stop .= "<li>" . $lang['sess_error'] . "</li>";
    }
    if ($is_logged) {
        $user_id = $member_id['user_id'];
    } else {
        $user_id = null;
    }
    if (!isset($_POST['id'])) {
        msgbox('خطا', 'فرمی یافت نشد!');
        return false;
    }

    $id = intval($_POST['id']);
    $form = $db->super_query("SELECT * FROM " . PREFIX . "_jform WHERE id='{$id}' ");
    if (!$form) {
        msgbox('خطا', 'فرمی یافت نشد!');
        return false;
    }
    if ($form['active'] != 1) {
        msgbox('خطا', 'فرم غیرفعال است.');
        return false;
    }
    $data = json_decode(json_decode($form['data']));
    // access control system
    foreach($data as $afield) {
        if (isset($afield->role)){
            // if field has role, check with current user usergroup
            if ($member_id['user_group'] == $afield->role) {
                $access_data[] = $afield;
            }
        } else {
            $access_data[] = $afield;
        }
    }

    // extract required fields data
    foreach($access_data as $rfield) {
        if ($rfield->required == 'true' ){
            $required_fields[] = $rfield;
        }
    }
    // check Request for required fields
    foreach ($required_fields as $required) {
        // first check field type is file or not
        $check = $required->type == 'file' ? $_FILES["$required->name"]:$_POST["$required->name"];
        // Now check the field is not empty
        if (empty($check)) {
            $stop .= "<li> فیلد \"{$required->label}\" تکمیل نشده است.</li>";
        }
    }

    // check security code is enabled
    if ($form['security_code']){
        if( $_POST['sec_code'] != $_SESSION['sec_code_session'] OR !$_SESSION['sec_code_session'] ) {
            $stop .= "<li>" . $lang['recaptcha_fail'] . "</li>";
        }

        $_SESSION['sec_code_session'] = false;
    }

    if ($stop) {
        msgbox($lang['all_err_1'], "<ul>{$stop}</ul><a href=\"javascript:history.go(-1)\">{$lang['all_prev']}</a>");
    } else {
        
        include_once DLEPlugins::Check(ENGINE_DIR . '/classes/parse.class.php');
        $parse = new ParseFilter();

        // extract fields which have name (not fields like paragraph or header)
        foreach($access_data as $item) {
            if (isset($item->name) ){
                $form_fields[] =  $item;
            }
        }
        foreach ($form_fields as $field) {
            if ( ($field->type != 'file' AND !empty($_POST["$field->name"])) OR ( ($field->type == 'file') AND (!empty($_FILES["$field->name"]['tmp_name'])) ) OR ( ($field->type == 'file') AND ( array_sum($_FILES["$field->name"]['error']) == 0 ) )  ) {
                switch ($field->type) {
                    case 'file':
                        $flag = is_array($_FILES["$field->name"]['tmp_name']);
                        if ($flag){
                            // if multiple upload field has error, break
                            if (array_sum($_FILES["$field->name"]['error']) != 0) break;
                        }else {
                            // if singular upload field is empty, break
                            if ( (empty($_FILES["$field->name"]['tmp_name'])) ) break;
                        }
                        include_once (DLEPlugins::Check(ENGINE_DIR . '/classes/uploads/jform.upload.class.php'));
                        
                        $extensions = (isset($field->file_types) ? explode(',', $field->file_types) : null);
                        $max_size = (isset($field->file_size) ? intval($field->file_size) : null);
                        $uploader = new JformFileUploader($_FILES["$field->name"],$id,$extensions,$max_size);
                        $result = $uploader->FileUpload();
                        if (is_array($result) AND isset($result['error'])){
                            msgbox('خطا', $result['error'] . "<br><a href=\"javascript:history.go(-1)\">{$lang['all_prev']}</a>");
                            return false;
                        } else $form_data[$field->name] = $result;

                        break;

                    default:
                        if (is_array($_POST["$field->name"])) {
                            $newArray = array_map(function ($item) use ($parse) {
                                return stripslashes($parse->process(trim(strip_tags($item))));
                            }, $_POST["$field->name"]);
                            $form_data[$field->name] = implode('||', $newArray);
                        } else {
                            $form_data[$field->name] = stripslashes($parse->process(trim(strip_tags($_POST["$field->name"]))));
                        }
                        break;
                }
            }
        }
        // handle tracking code
        if ($form['tracking']) {
            $tracking_code = bin2hex(random_bytes(10));
        }
        $form_data = $db->safesql(json_encode($form_data, JSON_UNESCAPED_UNICODE));
  
        $db->query("INSERT INTO " . PREFIX . "_jform_data (user_id,form_id,form_data,tracking) values ('{$member_id['user_id']}', '$id', '$form_data', '$tracking_code')");
        // send pm to form creator,
        include_once DLEPlugins::Check(ENGINE_DIR . '/api/api.class.php');
        $dle_api->send_pm_to_user(intval($form['user_id']),'فرم جدید',"پیام جدیدی برای فرم {$form['title']} ارسال شده است. <br> <a href=\"{$config['http_home_url']}{$config['admin_path']}?mod=jform&amp;action=msg_short&amp;formid={$form['id']}\">مشاهده پیام‌ها</a>",'' );
        if ($form['tracking']) {
            $tpl->load_template('jfrom_tracking.tpl');
            $tpl->set('{tracking}', $tracking_code);
            $tpl->compile('content');
            $tpl->clear();
        } else msgbox('ارسال موفق', "فرم با موفقیت ارسال شد. <br> <br><a href=\"javascript:history.go(-1)\">{$lang['all_prev']}</a>");
    }

} else {

    if (!isset($_REQUEST['formid'])) {
        msgbox('خطا', 'فرمی یافت نشد!');
        return false;
    }
    $id = intval($_REQUEST['formid']);
    $form = $db->super_query("SELECT * FROM " . PREFIX . "_jform WHERE id='{$id}' ");
    if (!$form) {
        msgbox('خطا', 'فرمی یافت نشد!');
        return false;
    }
    if ($form['active'] != 1) {
        msgbox('خطا', 'فرم غیرفعال است.');
        return false;
    }
    // access control system
    $data = json_decode(json_decode($form['data']));
    foreach($data as $afield) {
        if (isset($afield->role)){
            // if field has role, check with current user usergroup
            if ($member_id['user_group'] == $afield->role) {
                $access_data[] = $afield;
            }
        } else {
            $access_data[] = $afield;
        }
    }
    $access_data = json_encode($access_data, JSON_UNESCAPED_UNICODE);
    $template_name = 'jform';
    $enc = " enctype=\"multipart/form-data\"";
    $js_array[] = 'engine/classes/js/form-render.min.js';
    if (!file_exists(TEMPLATE_DIR . '/' . $template_name . '.tpl')) {
        @header("HTTP/1.0 404 Not Found");
        if ($config['own_404'] and file_exists(ROOT_DIR . '/404.html')) {
            @header("Content-type: text/html; charset=" . $config['charset']);
            echo file_get_contents(ROOT_DIR . '/404.html');
            die();
        }
    }
    if ($form['security_code']) {
        $tpl->set( '[sec_code]', "" );
        $tpl->set( '[/sec_code]', "" );
        $tpl->set( '{code}', "<a onclick=\"reload(); return false;\" href=\"#\" title=\"{$lang['reload_code']}\"><span id=\"dle-captcha\"><img src=\"engine/modules/antibot/antibot.php\" alt=\"{$lang['reload_code']}\" width=\"160\" height=\"80\" /></span></a>" );
    } else {
        $tpl->set_block( "'\\[sec_code\\](.*?)\\[/sec_code\\]'si", "" );
        $tpl->set( '{code}', "" );
    }
    $tpl->load_template($template_name . '.tpl');
    $tpl->set('{form-title}', $form['title']);
    $tpl->set('{form-content}', '<div class="jform-render"></div>');
    $tpl->copy_template = "<form  method=\"post\" id=\"sendjform\" name=\"sendjform\" action=\"\"{$enc}>\n" . $tpl->copy_template . "
	<input name=\"send\" type=\"hidden\" value=\"send\" />
<input name=\"id\" type=\"hidden\" value=\"{$form['id']}\" />
<input name=\"user_hash\" type=\"hidden\" value=\"{$dle_login_hash}\" />
</form>";
    $onload_scripts[] = <<<HTML
	var formRenderInstance = $('.jform-render').formRender({
		formData: {$access_data}
    });
HTML;
    $tpl->compile('content');
    $tpl->clear();
}
