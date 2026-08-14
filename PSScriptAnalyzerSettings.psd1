@{
    # Hugo helper scripts use Write-Host for interactive CLI output.
    ExcludeRules = @(
        'PSAvoidUsingWriteHost',
        'PSUseBOMForUnicodeEncodedFile'
    )
}
