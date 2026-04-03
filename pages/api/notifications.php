<?php
/* ============================================================
   API: Notifications
   Actions: get_unread, mark_read, mark_all_read
   ============================================================ */
require_once '../../config/config.php';
require_once '../../config/database.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode(['success'=>false,'message'=>'Not authenticated.']); exit;
}

$data   = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$action = $data['action'] ?? '';
$db     = Database::getInstance();
$uid    = (int)(current_user()['id']);

if ($action === 'get_unread') {
    $notifs = $db->prepare("SELECT * FROM notifications WHERE user_id=? AND is_read=0 ORDER BY created_at DESC LIMIT 20",'i',$uid);
    $count  = (int)($db->prepareOne("SELECT COUNT(*) AS c FROM notifications WHERE user_id=? AND is_read=0",'i',$uid)['c'] ?? 0);
    echo json_encode(['success'=>true,'notifications'=>$notifs,'count'=>$count]);
    exit;
}

if ($action === 'mark_read') {
    $nid = (int)($data['id'] ?? 0);
    $db->execute("UPDATE notifications SET is_read=1 WHERE id=? AND user_id=?",'ii',$nid,$uid);
    echo json_encode(['success'=>true]);
    exit;
}

if ($action === 'mark_all_read') {
    $db->execute("UPDATE notifications SET is_read=1 WHERE user_id=?",'i',$uid);
    echo json_encode(['success'=>true,'message'=>'All marked as read.']);
    exit;
}

echo json_encode(['success'=>false,'message'=>'Unknown action.']);
