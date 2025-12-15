<?php if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $note = strip_tags($_POST['note']);
    file_put_contents('notes.txt', $note . PHP_EOL, FILE_APPEND);
    header('Location: pomodoro.php');
}
?>