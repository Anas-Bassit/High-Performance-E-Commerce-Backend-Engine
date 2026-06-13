$Url = "http://localhost:8080/api/orders/place-async"
$ConcurrentRequests = 100

$Headers = @{
    "Content-Type" = "application/json"
    "Accept" = "application/json"
}

$StartTime = Get-Date

Write-Host "Starting stress test with $ConcurrentRequests concurrent requests..."
Write-Host "Target: $Url"
Write-Host ""

$Jobs = 1..$ConcurrentRequests | ForEach-Object {
    Start-Job -ScriptBlock {
        param($Url, $Headers, $RequestNumber)

        $Body = @{
            user_id = 1
            product_id = 2
            quantity = 1
        } | ConvertTo-Json

        $RequestStart = Get-Date

        try {
            $Response = Invoke-WebRequest `
                -Uri $Url `
                -Method POST `
                -Headers $Headers `
                -Body $Body `
                -UseBasicParsing

            $RequestEnd = Get-Date
            $DurationMs = ($RequestEnd - $RequestStart).TotalMilliseconds

            return [PSCustomObject]@{
                RequestNumber = $RequestNumber
                StatusCode = $Response.StatusCode
                Success = $true
                DurationMs = [math]::Round($DurationMs, 2)
                Error = $null
            }
        }
        catch {
            $RequestEnd = Get-Date
            $DurationMs = ($RequestEnd - $RequestStart).TotalMilliseconds

            $StatusCode = $null

            if ($_.Exception.Response -ne $null) {
                $StatusCode = [int]$_.Exception.Response.StatusCode
            }

            return [PSCustomObject]@{
                RequestNumber = $RequestNumber
                StatusCode = $StatusCode
                Success = $false
                DurationMs = [math]::Round($DurationMs, 2)
                Error = $_.Exception.Message
            }
        }
    } -ArgumentList $Url, $Headers, $_
}

$Results = $Jobs | Wait-Job | Receive-Job
$Jobs | Remove-Job

$EndTime = Get-Date
$TotalDuration = ($EndTime - $StartTime).TotalSeconds

$SuccessCount = ($Results | Where-Object { $_.Success -eq $true }).Count
$FailedCount = ($Results | Where-Object { $_.Success -eq $false }).Count
$AverageDuration = ($Results | Measure-Object -Property DurationMs -Average).Average
$MaxDuration = ($Results | Measure-Object -Property DurationMs -Maximum).Maximum
$MinDuration = ($Results | Measure-Object -Property DurationMs -Minimum).Minimum

Write-Host ""
Write-Host "Stress Test Result"
Write-Host "------------------"
Write-Host "Total Requests: $ConcurrentRequests"
Write-Host "Successful Requests: $SuccessCount"
Write-Host "Failed Requests: $FailedCount"
Write-Host "Total Test Duration: $([math]::Round($TotalDuration, 2)) seconds"
Write-Host "Average Response Time: $([math]::Round($AverageDuration, 2)) ms"
Write-Host "Min Response Time: $MinDuration ms"
Write-Host "Max Response Time: $MaxDuration ms"

Write-Host ""
Write-Host "Status Codes:"
$Results | Group-Object StatusCode | ForEach-Object {
    Write-Host "$($_.Name): $($_.Count)"
}

Write-Host ""
Write-Host "Failed Requests:"
$Results | Where-Object { $_.Success -eq $false } | Select-Object -First 10 | Format-Table