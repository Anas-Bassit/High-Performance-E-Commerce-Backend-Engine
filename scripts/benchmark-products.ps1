$NoCacheUrl = "http://localhost:8080/api/products/popular-no-cache"
$CacheUrl = "http://localhost:8080/api/products/popular"
$ClearCacheUrl = "http://localhost:8080/api/products/cache/popular"

$Iterations = 100

function Run-Benchmark {
    param (
        [string]$Name,
        [string]$Url,
        [int]$Iterations
    )

    Write-Host ""
    Write-Host "Running benchmark: $Name"
    Write-Host "URL: $Url"
    Write-Host "Iterations: $Iterations"
    Write-Host "-----------------------------"

    $Results = @()

    for ($i = 1; $i -le $Iterations; $i++) {
        $Start = Get-Date

        try {
            $Response = Invoke-WebRequest `
                -Uri $Url `
                -Method GET `
                -Headers @{ "Accept" = "application/json" } `
                -UseBasicParsing

            $End = Get-Date
            $DurationMs = ($End - $Start).TotalMilliseconds

            $Results += [PSCustomObject]@{
                RequestNumber = $i
                StatusCode = $Response.StatusCode
                Success = $true
                DurationMs = [math]::Round($DurationMs, 2)
            }
        }
        catch {
            $End = Get-Date
            $DurationMs = ($End - $Start).TotalMilliseconds

            $StatusCode = $null
            if ($_.Exception.Response -ne $null) {
                $StatusCode = [int]$_.Exception.Response.StatusCode
            }

            $Results += [PSCustomObject]@{
                RequestNumber = $i
                StatusCode = $StatusCode
                Success = $false
                DurationMs = [math]::Round($DurationMs, 2)
            }
        }
    }

    $SuccessCount = ($Results | Where-Object { $_.Success -eq $true }).Count
    $FailedCount = ($Results | Where-Object { $_.Success -eq $false }).Count
    $AverageDuration = ($Results | Measure-Object -Property DurationMs -Average).Average
    $MinDuration = ($Results | Measure-Object -Property DurationMs -Minimum).Minimum
    $MaxDuration = ($Results | Measure-Object -Property DurationMs -Maximum).Maximum

    Write-Host "Result: $Name"
    Write-Host "Successful Requests: $SuccessCount"
    Write-Host "Failed Requests: $FailedCount"
    Write-Host "Average Response Time: $([math]::Round($AverageDuration, 2)) ms"
    Write-Host "Min Response Time: $MinDuration ms"
    Write-Host "Max Response Time: $MaxDuration ms"

    return [PSCustomObject]@{
        Name = $Name
        SuccessfulRequests = $SuccessCount
        FailedRequests = $FailedCount
        AverageMs = [math]::Round($AverageDuration, 2)
        MinMs = $MinDuration
        MaxMs = $MaxDuration
    }
}

Write-Host "Clearing Redis cache before benchmark..."
Invoke-WebRequest -Uri $ClearCacheUrl -Method DELETE -UseBasicParsing | Out-Null

$NoCacheResult = Run-Benchmark `
    -Name "Popular Products Without Cache" `
    -Url $NoCacheUrl `
    -Iterations $Iterations

Write-Host ""
Write-Host "Clearing Redis cache before cached endpoint warmup..."
Invoke-WebRequest -Uri $ClearCacheUrl -Method DELETE -UseBasicParsing | Out-Null

Write-Host "Warming up Redis cache..."
Invoke-WebRequest -Uri $CacheUrl -Method GET -UseBasicParsing | Out-Null

$CacheResult = Run-Benchmark `
    -Name "Popular Products With Redis Cache" `
    -Url $CacheUrl `
    -Iterations $Iterations

Write-Host ""
Write-Host "Final Comparison"
Write-Host "================"
$NoCacheResult
$CacheResult

$Improvement = (($NoCacheResult.AverageMs - $CacheResult.AverageMs) / $NoCacheResult.AverageMs) * 100
Write-Host ""
Write-Host "Average Response Time Improvement: $([math]::Round($Improvement, 2))%"