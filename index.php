<?php
// MP3 파일 경로를 저장한 배열 생성
$music_dir = './music/';
$music_files = glob($music_dir . '*.mp3');
$music_per_page = 10;

// 검색어가 있을 시 파일 필터링
if(isset($_GET['search']) && $_GET['search'] !== ''){
    $search = $_GET['search'];
    $searched_music_files = glob($music_dir . '*'.$search.'*.mp3');
    $music_files = !empty($searched_music_files) ? $searched_music_files : [];
}

$page = isset($_GET['page']) ? $_GET['page'] : 1;
$start = ($page - 1) * $music_per_page;
$end = $start + $music_per_page;
$total = count($music_files);
$total_pages = ceil($total / $music_per_page);

// 선택된 MP3 파일 재생하는 JavaScript 함수 정의
echo "<script>";
echo "function play_music(music) {";
echo "var audio_player = document.getElementById('audio-player');";
echo "audio_player.src = music;";
echo "audio_player.play();";
echo "}";
echo "</script>";

// MP3 파일 업로드 구현
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 파일 경로 및 확장자 확인
    $upload_dir = './music/';
    $allowed_types = ['mp3'];
    $max_size = 2024 * 2024 * 20; // 20MB 용량 제한

    // 오류가 발생한 경우 알림
    if (!isset($_FILES['music_file'])) {
        echo "<script>alert('음악 파일을 선택해주세요.');</script>";
        return;
    }

    // 파일 이름, 확장자 및 크기 가져오기
    $file_name = $_FILES['music_file']['name'];
    $file_type = $_FILES['music_file']['type'];
    $file_size = $_FILES['music_file']['size'];
    $file_temp = $_FILES['music_file']['tmp_name'];
    
    // 확장자 검사 후 오류 발생 시 알림
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    if (!in_array($file_ext, $allowed_types)) {
        echo "<script>alert('mp3 파일만 업로드 가능합니다.');</script>";
        return;
    }

    // 용량 검사 후 오류 발생 시 알림
    if ($file_size > $max_size) {
        echo "<script>alert('파일 용량이 초과되었습니다. 20MB 이하의 파일만 업로드 가능합니다.');</script>";
        return;
    }

    // 파일을 업로드 가능한 디렉토리로 이동
    if (move_uploaded_file($file_temp, $upload_dir . $file_name)) {
        echo "<script>alert('음악 파일이 업로드 되었습니다.');</script>";
    } else {
        echo "<script>alert('음악 파일 업로드에 실패했습니다.');</script>";
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>MP3 플레이어</title>
</head>
<body>
    <h1>MP3 플레이어</h1>
    
    <!-- MP3 파일 검색 -->
    <form method="get" action="">
        <input type="text" name="search" placeholder="노래 제목 검색" value="<?php echo isset($_GET['search']) ? $_GET['search'] : ''; ?>">
        <input type="submit" value="검색">
    </form>
    
    <!-- MP3 음악 파일 목록을 표시할 리스트 생성 -->
    <ul id="music-list">
        <!-- PHP에서 생성한 MP3 파일 리스트 추가 -->
        <?php
        for ($i = $start; $i < $end; $i++) {
            if (isset($music_files[$i])) {
                $music = $music_files[$i];
                $music_name = basename($music, '.mp3');
                echo "<li><a href='#' onclick=\"javascript:play_music('$music')\">" . htmlspecialchars($music_name) . "</a></li>";
            }
        }
        ?>
    </ul>

    <!-- MP3 파일 업로드 폼 생성 -->
    <form enctype="multipart/form-data" method="post" action="">
        <input type="file" name="music_file" id="music_file">
        <input type="submit" value="파일 업로드">
    </form>
    
    <!-- 페이지 링크 생성 -->
    <?php
    if ($total_pages > 1) {
        echo "<div>페이지:";
        for ($i = 1; $i <= $total_pages; $i++) {
            if ($i == $page) {
                echo " <strong>{$i}</strong>";
            } else {
                echo " <a href=\"?page={$i}" .
                        (!empty($search) ? "&search={$search}" : '') . 
                        "\">{$i}</a>";
            }
        }
        echo "</div>";
    }
    ?>
    
    <!-- MP3 오디오 재생 컨트롤을 생성 -->
    <audio id="audio-player">
        이 브라우저는 HTML5 오디오 태그를 지원하지 않습니다.
    </audio>
</body>
</html>
