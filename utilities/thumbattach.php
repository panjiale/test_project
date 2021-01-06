<?php

/*
	[DISCUZ!] utilities/thumbattach.php
	This is NOT a freeware, use is subject to license terms

	Last Modified: 2006-12-28 15:05
*/

require_once './include/common.inc.php';

@set_time_limit(0);

echo '����ͼ�������ɹ���<hr>';

$do = !empty($do) ? $do : '';

if($do == '1') {
	$sqladd = "AND thumb=0";
} elseif($do == '2') {
	$sqladd = '';
} else {
	echo '<a href="?do=1">������ȱ������ͼ�ĸ���</a><br>';
	echo '<a href="?do=2">�������и���</a>';
	exit;
}

$next = !empty($next) ? intval($next) : 0;

$query = $db->query("SELECT * FROM {$tablepre}attachments WHERE isimage=1 $sqladd LIMIT $next, 10");

if(!$thumbstatus) {
	echo '������̳û�п�������ͼ���ܣ��뿪������ִ�д˳���';
	exit;
}

$thumbcount = !empty($thumbcount) ? $thumbcount : 0;
$imagecount = !empty($imagecount) ? $imagecount : 0;

if($db->num_rows($query)) {

	while($attachments = $db->fetch_array($query)) {

		$target		= 'attachments/'.$attachments['attachment'];
		$attachinfo	= getimagesize($target);
		$img_w		= $attachinfo[0];
		$img_h		= $attachinfo[1];

		$animatedgif = 0;
		if($attachinfo['mime'] == 'image/gif') {
			$fp = fopen($target, 'rb');
			$attachedfile = fread($fp, $attachments['filesize']);
			fclose($fp);
			$animatedgif = strpos($attachedfile, 'NETSCAPE2.0') === FALSE ? 0 : 1;
		}

		if(!$animatedgif && ($img_w >= $thumbwidth || $img_h >= $thumbheight)) {

			switch($attachinfo['mime']) {
				case 'image/jpeg':
					$attach_photo = imageCreateFromJPEG($target);
					break;
				case 'image/gif':
					$attach_photo = imageCreateFromGIF($target);
					break;
				case 'image/png':
					$attach_photo = imageCreateFromPNG($target);
					break;
			}

			$x_ratio = $thumbwidth / $img_w;
			$y_ratio = $thumbheight / $img_h;

			if(($x_ratio * $img_h) < $thumbheight) {
				$thumb['height'] = ceil($x_ratio * $img_h);
				$thumb['width'] = $thumbwidth;
			} else {
				$thumb['width'] = ceil($y_ratio * $img_w);
				$thumb['height'] = $thumbheight;
			}

			$thumb_photo = imagecreatetruecolor($thumb['width'], $thumb['height']);
			imageCopyreSampled($thumb_photo, $attach_photo ,0, 0, 0, 0, $thumb['width'], $thumb['height'], $img_w, $img_h);
			imageJPEG($thumb_photo, 'attachments/'.$attachments['attachment'].'.thumb.jpg');
			$db->query("UPDATE {$tablepre}attachments SET thumb=1 WHERE aid='$attachments[aid]'", 'UNBUFFERED');

			$thumbcount++;

		}

		$imagecount++;

	}

	echo "ͼƬ�������� $imagecount ��������ͼ���� $thumbcount ��......";

	redirect("?next=".($next+10)."&imagecount=$imagecount&thumbcount=$thumbcount&do=$do");

} else {

	echo "ͼƬ�������� $imagecount ��������ͼ���� $thumbcount ����<br><br><a href=\"?\">����ִ��</a>";

}

function redirect($url) {

	echo "<script>",
		"function redirect() {window.location.replace('$url');}\n",
		"setTimeout('redirect();', 500);\n",
		"</script>",
		"<br><br><a href=\"$url\">��������Զ���תҳ�棬�����˹���Ԥ��<br>���ǵ����������û���Զ���תʱ����������</a>";
	flush();

}

?>