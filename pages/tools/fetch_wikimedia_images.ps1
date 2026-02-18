$ErrorActionPreference = "Stop"

$uploads = Join-Path (Get-Location) "uploads"
if (!(Test-Path $uploads)) {
  New-Item -ItemType Directory -Path $uploads | Out-Null
}

$categories = @(
  @{ key = "gitar"; cm = "Category:Electric_guitars"; search = @("electric guitar", "stratocaster", "telecaster", "les paul guitar") },
  @{ key = "basszus"; cm = "Category:Bass_guitars"; search = @("bass guitar", "electric bass guitar", "jazz bass") },
  @{ key = "dob"; cm = "Category:Drum-kits"; search = @("drum kit", "drum set", "electronic drum kit") },
  @{ key = "billentyu"; cm = "Category:Electronic_keyboards"; search = @("electronic keyboard", "digital piano", "synthesizer keyboard") },
  @{ key = "mikrofon"; cm = "Category:Microphones"; search = @("microphone", "studio microphone", "dynamic microphone") },
  @{ key = "hangfal"; cm = "Category:Loudspeakers"; search = @("loudspeaker", "studio monitor speaker", "PA speaker") },
  @{ key = "tartozek"; cm = "Category:Musical_instrument_parts_and_accessories"; search = @("guitar strap", "guitar strings", "instrument cable", "drum sticks") }
)

$api = "https://commons.wikimedia.org/w/api.php"
$ua = @{ "User-Agent" = "SH9.1/1.0 (powershell)" }

function Get-CategoryFiles([string]$categoryTitle, [int]$limit = 1200) {
  $titles = New-Object System.Collections.Generic.List[string]
  $cont = $null
  do {
    $params = @{
      action = "query"
      format = "json"
      list = "categorymembers"
      cmtitle = $categoryTitle
      cmtype = "file"
      cmlimit = "500"
    }
    if ($cont) { $params.cmcontinue = $cont }
    $res = Invoke-RestMethod -Uri $api -Headers $ua -Method Get -Body $params -TimeoutSec 60
    foreach ($m in $res.query.categorymembers) {
      if ($m.title -like "File:*") { $titles.Add($m.title) }
      if ($titles.Count -ge $limit) { break }
    }
    $cont = $res.continue.cmcontinue
  } while ($cont -and $titles.Count -lt $limit)
  return $titles
}

function Get-ImageUrls([string[]]$titles) {
  $pairs = @()
  for ($i = 0; $i -lt $titles.Count; $i += 15) {
    $max = [Math]::Min($i + 14, $titles.Count - 1)
    $batch = $titles[$i..$max]
    $params = @{
      action = "query"
      format = "json"
      prop = "imageinfo"
      iiprop = "url"
      titles = ($batch -join "|")
    }
    $res = Invoke-RestMethod -Uri $api -Headers $ua -Method Get -Body $params -TimeoutSec 60
    foreach ($p in $res.query.pages.PSObject.Properties.Value) {
      if ($p.imageinfo -and $p.imageinfo.Count -gt 0) {
        $u = [string]$p.imageinfo[0].url
        if ($u -match "\.(jpe?g|png|webp)(\?|$)") {
          $pairs += [pscustomobject]@{
            title = [string]$p.title
            url   = $u
          }
        }
      }
    }
    Start-Sleep -Milliseconds 100
  }
  return $pairs
}

function Search-FileTitles([string]$term, [int]$limit = 300) {
  $titles = New-Object System.Collections.Generic.List[string]
  $offset = 0
  while ($titles.Count -lt $limit) {
    $params = @{
      action = "query"
      format = "json"
      list = "search"
      srnamespace = "6"
      srlimit = "50"
      srsearch = $term
      sroffset = [string]$offset
    }
    $res = Invoke-RestMethod -Uri $api -Headers $ua -Method Get -Body $params -TimeoutSec 60
    $items = @($res.query.search)
    if ($items.Count -eq 0) { break }
    foreach ($it in $items) {
      $title = [string]$it.title
      if ($title -like "File:*") { $titles.Add($title) }
      if ($titles.Count -ge $limit) { break }
    }
    if (-not $res.continue.sroffset) { break }
    $offset = [int]$res.continue.sroffset
    Start-Sleep -Milliseconds 100
  }
  return $titles
}

$manifest = @()
foreach ($c in $categories) {
  $key = $c.key
  Write-Output "Collecting $key ..."

  Get-ChildItem $uploads -File -Filter "${key}_???.jpg" -ErrorAction SilentlyContinue | Remove-Item -Force -ErrorAction SilentlyContinue

  $titles = New-Object System.Collections.Generic.List[string]
  (Get-CategoryFiles -categoryTitle $c.cm -limit 1200) | ForEach-Object { $titles.Add($_) }
  foreach ($term in @($c.search)) {
    (Search-FileTitles -term $term -limit 400) | ForEach-Object { $titles.Add($_) }
  }
  $seenTitle = New-Object "System.Collections.Generic.HashSet[string]"
  $dedup = New-Object System.Collections.Generic.List[string]
  foreach ($t in $titles) {
    if ($seenTitle.Add($t)) { $dedup.Add($t) }
  }
  $pairs = Get-ImageUrls -titles (@($dedup) | Select-Object -First 1000)

  $seenUrl = New-Object "System.Collections.Generic.HashSet[string]"
  $seenName = New-Object "System.Collections.Generic.HashSet[string]"
  $idx = 1

  foreach ($p in $pairs) {
    if ($idx -gt 50) { break }
    if ($seenUrl.Contains($p.url)) { continue }

    $name = ($p.title -replace "^File:", "" -replace "\.[A-Za-z0-9]+$", "" -replace "_", " ").Trim()
    if (-not $name) { continue }
    $low = $name.ToLowerInvariant()
    if ($low -match "logo|poster|sheet|diagram|icon|flag|map|coat of arms|symbol") { continue }
    if ($seenName.Contains($name)) { continue }

    $ext = ".jpg"
    if ($p.url -match "\.(png)(\?|$)") { $ext = ".png" }
    elseif ($p.url -match "\.(webp)(\?|$)") { $ext = ".webp" }
    $fname = "{0}_{1:d3}{2}" -f $key, $idx, $ext
    $dest = Join-Path $uploads $fname

    try {
      Invoke-WebRequest -Uri $p.url -Headers $ua -OutFile $dest -TimeoutSec 90
      if ((Get-Item $dest).Length -lt 5000) {
        Remove-Item $dest -Force
        continue
      }
    } catch {
      continue
    }

    $seenUrl.Add($p.url) | Out-Null
    $seenName.Add($name) | Out-Null
    $manifest += [pscustomobject]@{
      category   = $key
      index      = $idx
      name       = $name
      image_path = "/uploads/$fname"
      source_url = $p.url
    }
    $idx++
    Start-Sleep -Milliseconds 100
  }

  Write-Output ("{0}: {1} images" -f $key, ($idx - 1))
}

$manifestPath = Join-Path $uploads "instrument_images_manifest.json"
$manifest | ConvertTo-Json -Depth 4 | Set-Content -Encoding UTF8 $manifestPath
Write-Output "Saved manifest: $manifestPath"
