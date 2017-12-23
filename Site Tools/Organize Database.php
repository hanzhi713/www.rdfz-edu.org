<?php
if (isset($_GET['user']) && isset($_GET['password'])){
	$mysqli = new mysqli('localhost', $_GET['user'], $_GET['password'], 'edures_cn');

	$users = $mysqli->query('SELECT id FROM userlist ORDER BY id ASC');
	$start = 0;
	while ($user = $users->fetch_assoc()){
		$id = $user['id'];
		if (($id-$start)>1){
			$nid = $start + 1;
			$mysqli->query("UPDATE comments SET userid=$nid WHERE userid=$id");
			$mysqli->query("UPDATE replies SET userid=$nid WHERE userid=$id");
			$mysqli->query("UPDATE episodes SET authorid=$nid WHERE authorid=$id");
			$mysqli->query("UPDATE userlist SET id=$nid WHERE id=$id");
		}
		$start += 1;
	}

	$start=0;
	$comments = $mysqli->query('SELECT id FROM comments ORDER BY id ASC');
	while ($comment = $comments->fetch_assoc()){
		$id = $comment['id'];
		if (($id-$start)>1){
			$nid = $start + 1;
			$mysqli->query("UPDATE replies SET comment_id=$nid WHERE comment_id=$id");
			$mysqli->query("UPDATE comments SET id=$nid WHERE id=$id");
		}
		$start += 1;
	}

	$start=0;
	$replies = $mysqli->query('SELECT id FROM replies order by id asc');
	while ($reply = $replies->fetch_assoc()){
		$id = $reply['id'];
		if (($id-$start)>1){
			$nid = $start + 1;
			$mysqli->query("UPDATE replies SET id=$nid WHERE id=$id");
		}
		$start += 1;
	}
	$mysqli->close();
}
?>