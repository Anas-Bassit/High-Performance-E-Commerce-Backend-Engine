$uri = "http://localhost:8080/api/orders/place-distributed-lock"

$headers = @{
    Accept = "application/json"
}

$body = @{
    user_id = 1
    product_id = 1
    quantity = 1
} | ConvertTo-Json -Compress

$jobs = 1..20 | ForEach-Object {
    Start-Job -ScriptBlock {
        param($index, $uri, $body, $headers)

        $response = Invoke-WebRequest `
            -Uri $uri `
            -Method Post `
            -ContentType "application/json" `
            -Headers $headers `
            -Body $body `
            -UseBasicParsing `
            -SkipHttpErrorCheck

        $content = $response.Content
        $message = $content

        try {
            $json = $content | ConvertFrom-Json
            $message = $json.message
        } catch {
            $message = ($content -replace "\s+", " ")
            if ($message.Length -gt 180) {
                $message = $message.Substring(0, 180) + "..."
            }
        }

        [PSCustomObject]@{
            Request = $index
            Status  = [int]$response.StatusCode
            Message = $message
        }
    } -ArgumentList $_, $uri, $body, $headers
}

$results = $jobs | Receive-Job -Wait -AutoRemoveJob

$results | Sort-Object Request | Format-Table -AutoSize

Write-Host ""
Write-Host "Successful orders:" ($results | Where-Object { $_.Message -like "*distributed lock*" }).Count
Write-Host "Insufficient stock:" ($results | Where-Object { $_.Message -like "*Insufficient stock*" }).Count
Write-Host "Server errors:" ($results | Where-Object { $_.Status -ge 500 }).Count
Write-Host "Not found errors:" ($results | Where-Object { $_.Status -eq 404 }).Count