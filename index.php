<?php
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["submit"])) {
    $directory = __DIR__; // Current directory path
    $zip = new ZipArchive();
    $zipFileName = 'archive.zip';

    if ($zip->open($zipFileName, ZipArchive::CREATE) === TRUE) {
        // Get selected files from POST data
        if (isset($_POST['files']) && is_array($_POST['files'])) {
            foreach ($_POST['files'] as $file) {
                // Add file or folder to zip
                if (is_dir($directory . '/' . $file)) {
                    addFolderToZip($zip, $directory . '/' . $file, $file);
                } else {
                    $zip->addFile($directory . '/' . $file, $file);
                }
            }
        }

        $zip->close();

        // Download the zip file
        header("Content-type: application/zip");
        header("Content-Disposition: attachment; filename=$zipFileName");
        header("Pragma: no-cache");
        header("Expires: 0");
        readfile("$zipFileName");

        // Delete the zip file after download
        unlink($zipFileName);
    } else {
        echo "Failed to create zip file";
    }
}

function addFolderToZip($zip, $folderPath, $folderName) {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($folderPath),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iterator as $file) {
        if ($file->isFile()) {
            $filePath = $file->getRealPath();
            $relativePath = substr($filePath, strlen($folderPath) + 1);
            $zip->addFile($filePath, $folderName . '/' . $relativePath);
        }
    }
}
?>

<form method="post">
    <?php
    $directory = __DIR__; // Current directory path
    $files = array_diff(scandir($directory), array('..', '.'));

    echo '<label><input type="checkbox" id="select-all"> Select All</label><br>';

    foreach ($files as $file) {
        if (is_dir($directory . '/' . $file)) {
            echo '<label><input type="checkbox" class="folder-checkbox" name="files[]" value="' . $file . '"> ' . $file . '</label><br>';
        } else {
            echo '<label><input type="checkbox" class="file-checkbox" name="files[]" value="' . $file . '"> ' . $file . '</label><br>';
        }
    }
    ?>
    <input type="submit" name="submit" value="Create Zip">
</form>

<script>
    document.getElementById('select-all').addEventListener('change', function() {
        var checkboxes = document.querySelectorAll('.folder-checkbox, .file-checkbox');
        for (var i = 0; i < checkboxes.length; i++) {
            checkboxes[i].checked = this.checked;
        }
    });
</script>
