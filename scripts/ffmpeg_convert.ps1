$inputDir = "E:\Pliki\Projects\TubeYou\public\uploads\videos\1"

Get-ChildItem -Path $inputDir -Filter *.mp4 | Where-Object {
    # tylko pliki ORYGINALNE (bez _360p, _480p, _720p)
    $_.BaseName -notmatch '_\d+p$'
} | ForEach-Object {

    $inputFile = $_.FullName
    $baseName  = $_.BaseName

    $out720 = Join-Path $inputDir ($baseName + "_720p.mp4")
    $out480 = Join-Path $inputDir ($baseName + "_480p.mp4")
    $out360 = Join-Path $inputDir ($baseName + "_360p.mp4")

    Write-Host "Processing: $inputFile"

    if (-not (Test-Path $out720)) {
        ffmpeg -n -i "$inputFile" -vf scale=-2:720 -c:v libx264 -crf 23 -c:a aac -b:a 128k "$out720"
    } else {
        Write-Host "Skip 720p"
    }

    if (-not (Test-Path $out480)) {
        ffmpeg -n -i "$inputFile" -vf scale=-2:480 -c:v libx264 -crf 23 -c:a aac -b:a 128k "$out480"
    } else {
        Write-Host "Skip 480p"
    }

    if (-not (Test-Path $out360)) {
        ffmpeg -n -i "$inputFile" -vf scale=-2:360 -c:v libx264 -crf 23 -c:a aac -b:a 128k "$out360"
    } else {
        Write-Host "Skip 360p"
    }
}