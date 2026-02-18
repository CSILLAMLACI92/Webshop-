$ErrorActionPreference = "Stop"

$cats = @(
  @{ key = "gitar"; q = "electric,guitar,instrument" },
  @{ key = "basszus"; q = "bass,guitar,instrument" },
  @{ key = "dob"; q = "drum,kit,instrument" },
  @{ key = "billentyu"; q = "keyboard,synthesizer,piano,instrument" },
  @{ key = "mikrofon"; q = "microphone,audio,studio" },
  @{ key = "hangfal"; q = "speaker,loudspeaker,monitor,audio" },
  @{ key = "tartozek"; q = "guitar,picks,strap,cable,accessory" }
)

$uploads = Join-Path (Get-Location) "uploads"

foreach ($c in $cats) {
  $key = $c.key
  $q = $c.q
  Write-Output "Downloading $key ..."

  Get-ChildItem $uploads -File -Filter "${key}_???.jpg" -ErrorAction SilentlyContinue |
    Remove-Item -Force -ErrorAction SilentlyContinue

  $i = 1
  $attempt = 0
  while ($i -le 50 -and $attempt -lt 500) {
    $attempt++
    $fname = "{0}_{1:d3}.jpg" -f $key, $i
    $dest = Join-Path $uploads $fname
    $url = "https://loremflickr.com/900/700/$q?lock=$attempt"
    try {
      Invoke-WebRequest -Uri $url -OutFile $dest -TimeoutSec 60
      $len = (Get-Item $dest).Length
      if ($len -lt 12000) {
        Remove-Item $dest -Force
        continue
      }
      $i++
      Start-Sleep -Milliseconds 120
    } catch {
      if (Test-Path $dest) { Remove-Item $dest -Force }
      Start-Sleep -Milliseconds 250
      continue
    }
  }

  Write-Output ("{0}: {1}/50" -f $key, ($i - 1))
}
