 <?php
/**
* install.php
* PLIST文件生成器，用于iOS渠道包一键安装。
* Created by pcjbird on 2015-05-28
* Copyright (c) 2015年 Zero Status. All rights reserved.
*/

//判断是否为字典数组（dict）
function isDict($array)
{
 return (is_array($array) && 0 !== count(array_diff_key($array, array_keys(array_keys($array)))));

}

//向xml节点中写入字典数组（dict）
function xmlWriteDict(XMLWriter $x, &$dict) 
{
 $x->startElement('dict');
 foreach($dict as $k => &$v) 
 {
 $x->writeElement('key', $k);
 xmlWriteValue($x, $v);
 }
 $x->endElement();
}
 
 //向xml节点中写入数组（array）
function xmlWriteArray(XMLWriter $x, &$arr) 
{
 $x->startElement('array');
 foreach($arr as &$v)
 xmlWriteValue($x, $v);
 $x->endElement();
}

//根据类型向xml节点中写入值
function xmlWriteValue(XMLWriter $x, &$v) 
{
 if (is_int($v) || is_long($v))
 $x->writeElement('integer', $v);
 elseif (is_float($v) || is_real($v) || is_double($v))
 $x->writeElement('real', $v);
 elseif (is_string($v))
 $x->writeElement('string', $v);
 elseif (is_bool($v))
 $x->writeElement($v?'true':'false');
 elseif (isDict($v))
 xmlWriteDict($x, $v);
 elseif (is_array($v))
 xmlWriteArray($x, $v);
 else 
 {
 trigger_error("Unsupported data type in plist ($v)", E_USER_WARNING);
 $x->writeElement('string', $v);
 }
}

//创建plist
function createplist()
{
 $ssl_server = 'https://www.lessney.com/';
 $target_name = 'Sample';
 $bundle_identifier = 'com.lessney.' . $target_name;
 $subtitle = 'Zero Status Inc.';
 $title = '示例程序';
 $versionname = $_GET['v'];
 if (!$versionname) 
 {
 $versionname = '1.0.0';
 }
 $versioncode = str_replace('.', '', $versionname);
 $channelid = $_GET['cid'];
 if (!$channelid) 
 {
 $channelid = '0';
 }

 header('Content-Type: application/xml');
 $plist = new XmlWriter();
 $plist->openMemory();
 $plist->setIndent(TRUE);
 $plist->startDocument('1.0', 'UTF-8');
 $plist->writeDTD('plist', '-//Apple//DTD PLIST 1.0//EN', 'http://www.apple.com/DTDs/PropertyList-1.0.dtd');
 $plist->startElement('plist');
 $plist->writeAttribute('version', '1.0');

 $pkg = array();
 $pkg['kind'] = 'software-package';
 $pkg['url'] = $ssl_server . $target_name .'_v' . $versioncode . '_' .$channelid . '.ipa';

 $displayimage = array();
 $displayimage['kind'] = 'display-image';
 $displayimage['needs-shine'] = TRUE;
 $displayimage['url'] = $ssl_server . 'Icon.png';

 $fullsizeimage = array();
 $fullsizeimage['kind'] = 'full-size-image';
 $fullsizeimage['needs-shine'] = TRUE;
 $fullsizeimage['url'] = $ssl_server . 'iTunesArtwork.png';

 $assets = array();
 $assets[] = $pkg;
 $assets[] = $displayimage;
 $assets[] = $fullsizeimage;

 $metadata = array();
 $metadata['bundle-identifier'] = $bundle_identifier;
 $metadata['bundle-version'] = $versionname;
 $metadata['kind'] = 'software';
 $metadata['subtitle'] = $subtitle;
 $metadata['title'] = $title;

 $items0 = array();
 $items0['assets'] = $assets;
 $items0['metadata'] = $metadata;

 $items = array();
 $items[] = $items0;

 $root = array();
 $root['items'] = $items;

 xmlWriteValue($plist, $root);

 $plist->endElement();
 $plist->endDocument();

 return $plist->outputMemory();
}

//输出plist
echo createplist();

?>
