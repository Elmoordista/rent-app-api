<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Transaction Report</title>
    <style>
        /* Set page margins */
        @page {
            margin: 30px 40px;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
            margin: 0;
            padding: 0;
        }
        header {
            text-align: center;
            margin-bottom: 25px;
            border-bottom: 1px solid #000;
            padding-bottom: 15px;
        }
        header img {
            height: 75px;
            width: auto;
        }
        header h2 {
            margin: 0;
            font-size: 22px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px 10px;
        }
        th {
            background-color: #f3f3f3;
            text-align: left;
        }
        td {
            vertical-align: middle;
        }
        td.amount {
            text-align: left;
        }
        td.status {
            text-align: center;
        }
        .status-badge {
            background-color: #28a745;
            color: white;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: 600;
            display: inline-block;
            text-transform: capitalize;
        }
    </style>
</head>
<body>
    <header>
        <img src="{{ public_path('images/logo.png') }}" alt="Company Logo">
        <h3>Recent Rentals Reports</h3>
        {{-- Optional header note below title if needed --}}
        {{-- <p>{{ $headerNote ?? '' }}</p> --}}
    </header>

    <table>
        <thead>
            <tr>
                <th>Category</th>
                <th>Renter</th>
                <th>Item</th>
                <th>Date</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($transaction as $item)
            <tr>
                <td>{{ $item['category'] }}</td>
                <td>{{ $item['renter'] }}</td>
                <td>{{ $item['item_name']}}</td>
                <td>{{ \Carbon\Carbon::parse($item['date'])->format('m/d/Y, h:i:s A') }}</td>
                <td class="amount">{{ number_format($item['income'], 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
