<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; margin: 20px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 20px; }
        .title { font-size: 24px; font-weight: bold; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { background-color: #f5f5f5; border: 1px solid #ddd; padding: 8px; text-align: left; font-weight: bold; }
        td { border: 1px solid #ddd; padding: 6px 8px; }
        .footer { margin-top: 30px; text-align: center; font-size: 10px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">{{ $title }}</div>
        <div>Transport Information Management System</div>
    </div>
    
    <p><strong>Generated:</strong> {{ $generatedAt }}</p>
    <p><strong>Total Records:</strong> {{ count($data) }}</p>
    
    @if(!empty($data))
    <table>
        <thead>
            <tr>
                @foreach($headers as $header)
                <th>{{ $header }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($data as $row)
            <tr>
                @foreach($row as $cell)
                <td>{{ $cell }}</td>
                @endforeach
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <p><em>No data available for the selected criteria.</em></p>
    @endif
    
    <div class="footer">
        <p>This report was generated automatically by the Transport IMS system.</p>
    </div>
</body>
</html>
