<?php

// P.M. Pack for Discuz! Version 1.0
// Translated by Crossday

// ATTENTION: Please add slashes(\) before (') and (")

$language = array
(

	'reason_moderate_subject' => '����������ⱻִ�й������',
	'reason_moderate_message' => '��������̳ϵͳ�Զ����͵�֪ͨ����Ϣ��

[b]����������������ⱻ [url={$boardurl}space.php?uid={$discuz_uid}][i]{$discuz_user}[/i][/url] ִ�� {$modaction} ������[/b]

[b]����:[/b] [url={$boardurl}viewthread.php?tid={$thread[tid]}]{$thread[subject]}[/url]
[b]����ʱ��:[/b] {$thread[dateline]}
[b]������̳:[/b] [url={$boardurl}forumdisplay.php?fid={$fid}]{$forumname}[/url]

[b]��������:[/b] {$reason}

������Ա�������������飬������ȡ����ϵ��',

	'reason_merge_subject' => '����������ⱻִ�кϲ�����',
	'reason_merge_message' => '��������̳ϵͳ�Զ����͵�֪ͨ����Ϣ��

[b]����������������ⱻ [url={$boardurl}space.php?uid={$discuz_uid}][i]{$discuz_user}[/i][/url] ִ�� {$modaction} ������[/b]

[b]����:[/b] {$thread[subject]}
[b]����ʱ��:[/b] {$thread[dateline]}
[b]������̳:[/b] [url={$boardurl}forumdisplay.php?fid={$fid}]{$forumname}[/url]

[b]�ϲ��������:[/b] [url={$boardurl}viewthread.php?tid={$thread[tid]}]{$other[subject]}[/url]

[b]��������:[/b] {$reason}

������Ա�������������飬������ȡ����ϵ��',
	'reason_delete_post_subject' => '������Ļظ���ִ�й������',
	'reason_delete_post_message' => '��������̳ϵͳ�Զ����͵�֪ͨ����Ϣ��

[b]������������Ļظ��� [url={$boardurl}space.php?uid={$discuz_uid}][i]{$discuz_user}[/i][/url] ִ�� {$modaction} ������[/b]
[quote]{$post[message]}[/quote]

[b]����ʱ��:[/b] {$post[dateline]}
[b]������̳:[/b] [url={$boardurl}forumdisplay.php?fid={$fid}]{$forumname}[/url]

[b]��������:[/b] {$reason}

������Ա�������������飬������ȡ����ϵ��',

	'reason_ban_post_subject' => '������Ļظ���ִ�й������',
	'reason_ban_post_message' => '��������̳ϵͳ�Զ����͵�֪ͨ����Ϣ��

[b]����������������ӱ� [url={$boardurl}space.php?uid={$discuz_uid}][i]{$discuz_user}[/i][/url] ִ�� {$modaction} ������[/b]
[quote]{$post[message]}[/quote]

[b]����ʱ��:[/b] {$post[dateline]}
[b]������̳:[/b] [url={$boardurl}forumdisplay.php?fid={$fid}]{$forumname}[/url]

[b]��������:[/b] {$reason}

������Ա�������������飬������ȡ����ϵ��',

	'reason_warn_post_subject' => '����������ӱ�ִ�й������',
	'reason_warn_post_message' => '��������̳ϵͳ�Զ����͵�֪ͨ����Ϣ��

[b]����������������ӱ� [url={$boardurl}space.php?uid={$discuz_uid}][i]{$discuz_user}[/i][/url] ִ�� {$modaction} ������[/b]
[quote]{$post[message]}[/quote]
�ۼ� $warninglimit �ξ��棬�������Զ���ֹ���� $warningexpiration �졣

[b]����ʱ��:[/b] {$post[dateline]}
[b]������̳:[/b] [url={$boardurl}forumdisplay.php?fid={$fid}]{$forumname}[/url]

[b]��������:[/b] {$reason}

������Ա�������������飬������ȡ����ϵ��',

	'reason_move_subject' => '����������ⱻִ�й������',
	'reason_move_message' => '��������̳ϵͳ�Զ����͵�֪ͨ����Ϣ��

[b]����������������ⱻ [url={$boardurl}space.php?uid={$discuz_uid}][i]{$discuz_user}[/i][/url] ִ�� �ƶ� ������[/b]

[b]����:[/b] [url={$boardurl}viewthread.php?tid={$thread[tid]}]{$thread[subject]}[/url]
[b]����ʱ��:[/b] {$thread[dateline]}
[b]ԭ��̳:[/b] [url={$boardurl}forumdisplay.php?fid={$fid}]{$forumname}[/url]
[b]Ŀ����̳:[/b] [url={$boardurl}forumdisplay.php?fid={$toforum[fid]}]{$toforum[name]}[/url]

[b]��������:[/b] {$reason}

������Ա�������������飬������ȡ����ϵ��',

	'rate_reason_subject' => '����������ӱ�����',
	'rate_reason_message' => '��������̳ϵͳ�Զ����͵�֪ͨ����Ϣ��

[b]����������������ӱ� [url={$boardurl}space.php?uid={$discuz_uid}][i]{$discuz_user}[/i][/url] ���֡�[/b]
[quote]{$post[message]}[/quote]

[b]����ʱ��:[/b] {$post[dateline]}
[b]������̳:[/b] [url={$boardurl}forumdisplay.php?fid={$fid}]{$forumname}[/url]
[b]��������:[/b] [url={$boardurl}viewthread.php?tid={$tid}&page={$page}#pid{$pid}]{$thread[subject]}[/url]

[b]���ַ���:[/b] {$ratescore}
[b]��������:[/b] {$reason}',

	'rate_removereason_subject' => '����������ӵ����ֱ�����',
	'rate_removereason_message' => '��������̳ϵͳ�Զ����͵�֪ͨ����Ϣ��

[b]���������������ӵ����ֱ� [url={$boardurl}space.php?uid={$discuz_uid}][i]{$discuz_user}[/i][/url] ������[/b]
[quote]{$post[message]}[/quote]

[b]����ʱ��:[/b] {$post[dateline]}
[b]������̳:[/b] [url={$boardurl}forumdisplay.php?fid={$fid}]{$forumname}[/url]
[b]��������:[/b] [url={$boardurl}viewthread.php?tid={$tid}&page={$page}#pid{$pid}]{$thread[subject]}[/url]

[b]���ַ���:[/b] {$ratescore}
[b]��������:[/b] {$reason}',

	'transfer_subject' => '���յ�һ�ʻ���ת��',
	'transfer_message' => '��������̳ϵͳ�Զ����͵�֪ͨ����Ϣ��

[b]���յ�һ���������˵Ļ���ת�ˡ�[/b]

[b]����:[/b] [url={$boardurl}space.php?uid={$discuz_uid}][i]{$discuz_user}[/i][/url]
[b]ʱ��:[/b] {$transfertime}
[b]����:[/b] {$extcredits[$creditstrans][title]} {$amount} {$extcredits[$creditstrans][unit]}
[b]������:[/b] {$extcredits[$creditstrans][title]} {$netamount} {$extcredits[$creditstrans][unit]}

[b]����:[/b] {$transfermessage}

������[url={$boardurl}memcp.php?action=creditslog]�������[/url]�������Ļ���ת����һ���¼��',

	'reportpost_subject'	=> '$discuz_user ��������һƪ����',
	'reportpost_message'	=> '[i]{$discuz_user}[/i] �����������µ����ӣ���ϸ���������:
[url]{$posturl}[/url]

��/���ı���������: {$reason}',

	'addfunds_subject' => '���ֳ�ֵ�ɹ����',
	'addfunds_message' => '��������̳ϵͳ�Զ����͵�֪ͨ����Ϣ��

[b]���ύ�Ļ��ֳ�ֵ�����ѳɹ���ɣ���Ӧ����Ļ����Ѿ��������Ļ����˻���[/b]

[b]������:[/b] {$order[orderid]}
[b]�ύʱ��:[/b] {$submitdate}
[b]ȷ��ʱ��:[/b] {$confirmdate}

[b]֧��:[/b] ����� {$order[price]} Ԫ
[b]����:[/b] {$extcredits[$creditstrans][title]} {$order[amount]} {$extcredits[$creditstrans][unit]}

������[url={$boardurl}memcp.php?action=creditslog]�������[/url]�������Ļ���ת����һ���¼��',

	'trade_seller_send_subject' => '����ҹ���������Ʒ',
	'trade_seller_send_message' => '��������̳ϵͳ�Զ����͵�֪ͨ����Ϣ��

��� {$user} ����������Ʒ {$itemsubject}

����Ѹ���ȴ�����������[url={$boardurl}trade.php?orderid={$orderid}]�������[/url]�鿴���顣',

	'trade_buyer_confirm_subject' => '���������Ʒ�Ѿ�����',
	'trade_buyer_confirm_message' => '��������̳ϵͳ�Զ����͵�֪ͨ����Ϣ��

���������Ʒ {$itemsubject}

���� {$user} �ѷ������ȴ�����ȷ�ϣ���[url={$boardurl}trade.php?orderid={$orderid}]�������[/url]�鿴���顣',

	'trade_fefund_success_subject' => '���������Ʒ�ѳɹ��˿�',
	'trade_fefund_success_message' => '��������̳ϵͳ�Զ����͵�֪ͨ����Ϣ��

��Ʒ {$itemsubject} ���˿�ɹ�����[url={$boardurl}trade.php?orderid={$orderid}]�������[/url]���Է����֡�',

	'trade_success_subject' => '��Ʒ�����ѳɹ����',
	'trade_success_message' => '��������̳ϵͳ�Զ����͵�֪ͨ����Ϣ��

��Ʒ {$itemsubject} �ѽ��׳ɹ�����[url={$boardurl}trade.php?orderid={$orderid}]�������[/url]���Է����֡�',

	'activity_apply_subject' => '���������ͨ����׼',
	'activity_apply_message' => '��������̳ϵͳ�Զ����͵�֪ͨ����Ϣ��

� [b]{$activity_subject}[/b] �ķ���������׼���μӴ˻����[url={$boardurl}viewthread.php?tid={$tid}]�������[/url]�鿴���顣',

	'activity_delete_subject' => '������Ļ�������߾ܾ�',
	'activity_delete_message' => '��������̳ϵͳ�Զ����͵�֪ͨ����Ϣ��

������Ļ [b]{$activity_subject}[/b] �ѱ������߾ܾ�����[url={$boardurl}viewthread.php?tid={$tid}]�������[/url]�鿴���顣',

	'reward_question_subject' => '����������ͱ���������Ѵ�',
	'reward_question_message' => '��������̳ϵͳ�Զ����͵�֪ͨ����Ϣ��

[b]����������ͱ� [url={$boardurl}space.php?uid={$discuz_uid}][i]{$discuz_user}[/i][/url] ������ ��Ѵ𰸡�[/b]

[b]����:[/b] [url={$boardurl}viewthread.php?tid={$thread[tid]}]{$thread[subject]}[/url]
[b]����ʱ��:[/b] {$thread[dateline]}
[b]������̳:[/b] [url={$boardurl}forumdisplay.php?fid={$fid}]{$forum[name]}[/url]

������Ա����������飬��������ȡ����ϵ��',

	'reward_bestanswer_subject' => '������Ļظ���ѡΪ��Ѵ�',
	'reward_bestanswer_message' => '��������̳ϵͳ�Զ����͵�֪ͨ����Ϣ��

[b]���Ļظ��� [url={$boardurl}space.php?uid={$discuz_uid}][i]{$discuz_user}[/i][/url] ѡΪ������Ѵ𰸡�[/b]

[b]����:[/b] [url={$boardurl}viewthread.php?tid={$thread[tid]}]{$thread[subject]}[/url]
[b]����ʱ��:[/b] {$thread[dateline]}
[b]������̳:[/b] [url={$boardurl}forumdisplay.php?fid={$fid}]{$forum[name]}[/url]

������Ա����������飬��������ȡ����ϵ��',

	'modthreads_delete_subject' => '��������������ʧ��',
	'modthreads_delete_message' => '��������̳ϵͳ�Զ����͵�֪ͨ����Ϣ��

[b]���ʧ��:[/b] ����������� [b][u] {$threadsubject} [/u][/b] û��ͨ����ˣ����ѱ�ɾ��!
[b]��������:[/b] {$reason}

������Ա�������������飬������ȡ����ϵ��',

	'modthreads_validate_subject' => '����������������ͨ��',
	'modthreads_validate_message' => '��������̳ϵͳ�Զ����͵�֪ͨ����Ϣ��

[b]���ͨ��:[/b] ����������� [url={$boardurl}viewthread.php?tid={$tid}]{$threadsubject}[/url] �Ѿ����ͨ��!
[b]��������:[/b] {$reason}

������Ա�������������飬������ȡ����ϵ��',

	'modreplies_delete_subject' => '������Ļظ����ʧ��',
	'modreplies_delete_message' => '��������̳ϵͳ�Զ����͵�֪ͨ����Ϣ��

[b]���ʧ��:[/b] ������ظ�û��ͨ����ˣ����ѱ�ɾ��!
[b]��������:[/b] [url={$boardurl}viewthread.php?tid={$tid}]��˲鿴[/url]
[b]�ظ�����:[/b]
[quote]
	$post
[/quote]
[b]��������:[/b] {$reason}

������Ա�������������飬������ȡ����ϵ��',

	'modreplies_validate_subject' => '������Ļظ������ͨ��',
	'modreplies_validate_message' => '��������̳ϵͳ�Զ����͵�֪ͨ����Ϣ��

[b]���ͨ��:[/b] ������Ļظ��Ѿ����ͨ����
[b]��������:[/b] [url={$boardurl}viewthread.php?tid={$tid}]��˲鿴[/url]
[b]�ظ�����:[/b]
[quote]
	$post
[/quote]
[b]��������:[/b] {$reason}

������Ա�������������飬������ȡ����ϵ��',

	'magics_sell_subject' => '���ĵ��߳ɹ�����',
	'magics_sell_message' => '���� {$magic[name]} ���߱� {$discuz_user} ���򣬻������ {$totalcredit}',

	'magics_receive_subject' => '���յ����������ĵ���',
	'magics_receive_message' => '���յ� {$discuz_user} �͸��� {$magicarray[$magicid][name]} ���ߣ��뵽�ҵĵ��������',

	'reason_copy_subject' => '����������ⱻִ�и��Ʋ���',
	'reason_copy_message' => '��������̳ϵͳ�Զ����͵�֪ͨ����Ϣ��

[b]����������������ⱻ [url={$boardurl}space.php?uid={$discuz_uid}][i]{$discuz_user}[/i][/url] ִ�� {$modaction} ������[/b]

[b]����:[/b] {$thread[subject]}
[b]����ʱ��:[/b] {$thread[dateline]}
[b]������̳:[/b] [url={$boardurl}forumdisplay.php?fid={$fid}]{$forumname}[/url]

[b]���ƺ������:[/b] [url={$boardurl}viewthread.php?tid=$threadid]{$thread[subject]}[/url]

[b]��������:[/b] {$reason}

������Ա�������������飬������ȡ����ϵ��',

	'eccredit_subject' => '��Ʒ���׵ĶԷ��Ѿ����ۣ������',
	'eccredit_message' => '��������̳ϵͳ�Զ����͵�֪ͨ����Ϣ��

[url={$boardurl}trade.php?orderid=$orderid]�鿴���׵�[/url]

�������׵� $discuz_user �Ѿ������������ۣ��뾡�����۶Է���',

	'buddy_new_subject' => '{$discuz_user} �����Ϊ����',
	'buddy_new_message' => '������ϵͳ�Զ����͵�֪ͨ����Ϣ��

{$discuz_user} �����Ϊ���ѡ�
������[url={$boardurl}space.php?uid=$discuz_uid]�������[/url]�鿴��(��)�ĸ������ϣ�����[url={$boardurl}my.php?item=buddylist&newbuddyid={$discuz_uid}&buddysubmit=yes]����(��)��Ϊ���ĺ���[/url]��',
	'buddy_new_uch_message' => '������ϵͳ�Զ����͵�֪ͨ����Ϣ��

{$discuz_user} �����Ϊ���ѡ�
������[url={$boardurl}space.php?uid=$discuz_uid]�������[/url]�鿴��(��)�ĸ������ϣ�����[url={$boardurl}my.php?item=buddylist&newbuddyid={$discuz_uid}&buddysubmit=yes]����(��)��Ϊ���ĺ���[/url]��
Ҳ����[url={$uchomeurl}/space.php?uid={$discuz_uid}]�������[/url]�鿴��(��)�ĸ��˿ռ䡣',

);

?>