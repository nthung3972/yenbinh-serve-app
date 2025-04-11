<!-- resources/views/exports/invoice.blade.php -->

<table style="width: 100%; font-family: 'Arial', sans-serif; border-collapse: collapse;">
    <!-- Logo và thông tin -->
    <tr>
        <td colspan="2" style="height: 80px; padding-left: 70px;">
            <!-- Chỗ cho logo -->
        </td>
    </tr>
    
    <!-- Tiêu đề -->
    <tr>
        <td colspan="4" style="text-align: center; font-size: 20px; font-weight: bold; padding: 20px 0; color: #2F528F;">
            HÓA ĐƠN THANH TOÁN DỊCH VỤ
        </td>
    </tr>
    
    <!-- Thông tin căn hộ -->
    <tr>
        <td colspan="4" style="text-align: center; padding: 5px 0;">
            <span style="display: inline-block; background-color: #D9E1F2; padding: 5px 15px; border-radius: 20px;">
                Mã căn hộ: <strong>{{ $invoice->apartment->apartment_number ?? '----' }}</strong>
            </span>
        </td>
    </tr>
    <tr>
        <td colspan="4" style="padding: 5px 0;">
            Ngày phát hành: <strong>{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d/m/Y') }}</strong>
        </td>
    </tr>
    <tr>
        <td colspan="4" style="padding: 5px 0;">
            Hạn thanh toán: <strong style="color: #C55A11;">{{ \Carbon\Carbon::parse($invoice->due_date)->format('d/m/Y') }}</strong>
        </td>
    </tr>
    
    <!-- Khoảng trống -->
    <tr>
        <td colspan="4" style="height: 10px;"></td>
    </tr>
    
    <!-- Header bảng -->
    <tr style="background-color: #4472C4; color: white;">
        <th style="padding: 12px; border: 1px solid #B4C6E7; text-align: center;">Loại Phí</th>
        <th style="padding: 12px; border: 1px solid #B4C6E7; text-align: center;">Số Lượng</th>
        <th style="padding: 12px; border: 1px solid #B4C6E7; text-align: center;">Đơn Giá (VNĐ)</th>
        <th style="padding: 12px; border: 1px solid #B4C6E7; text-align: center;">Thành Tiền (VNĐ)</th>
    </tr>
    
    <!-- Dòng dữ liệu -->
    @foreach($invoice->invoiceDetails as $index => $item)
    <tr>
        <td style="padding: 10px; border: 1px solid #B4C6E7;">
            {{ \App\Exports\InvoiceExport::getServiceName($item->service_name) }}
        </td>
        <td style="padding: 10px; border: 1px solid #B4C6E7; text-align: center;">{{ $item->quantity }}</td>
        <td style="padding: 10px; border: 1px solid #B4C6E7; text-align: right;">{{ number_format($item->price, 0, ',', '.') }}</td>
        <td style="padding: 10px; border: 1px solid #B4C6E7; text-align: right;">{{ number_format($item->amount, 0, ',', '.') }}</td>
    </tr>
    @endforeach
    
    <!-- Tổng tiền -->
    <tr>
        <td colspan="3" style="text-align: right; font-weight: bold; padding: 12px; border-top: 2px solid #4472C4;">
            Tổng tiền:
        </td>
        <td style="font-weight: bold; padding: 12px; text-align: right; border-top: 2px solid #4472C4; color: #70AD47;">
            {{ number_format($invoice->total_amount, 0, ',', '.') }} VNĐ
        </td>
    </tr>
    
    <!-- Khoảng trống -->
    <tr>
        <td colspan="4" style="height: 15px;"></td>
    </tr>
    
    <!-- Thông tin thanh toán -->
    <tr>
        <td colspan="4" style="padding: 10px; background-color: #D9E1F2; text-align: center; font-size: 13px; border-radius: 5px;">
            <strong>Thông tin thanh toán:</strong><br>
            Ngân hàng: <strong>VIETCOMBANK</strong> | STK: <strong>1234567890</strong> | Chủ TK: <strong>CTY YÊN BÌNH</strong><br>
            Nội dung: <strong>{{ $invoice->apartment->apartment_number ?? 'MÃ CĂN HỘ' }} THANH TOAN HOA DON {{ \Carbon\Carbon::parse($invoice->invoice_date)->format('m/Y') }}</strong>
        </td>
    </tr>
    
    <!-- Cảm ơn -->
    <tr>
        <td colspan="4" style="text-align: center; padding-top: 20px; font-style: italic;">
            Cảm ơn quý khách đã sử dụng dịch vụ của chúng tôi!
        </td>
    </tr>
    
    <!-- Phần chân hóa đơn -->
    <tr>
        <td colspan="4" style="padding-top: 5px; text-align: center; font-size: 12px; border-top: 1px solid #B4C6E7; margin-top: 10px;">
            -------------------------------------<br>
            <strong>Ban quản lý tòa nhà Yên Bình</strong><br>
            Hỗ trợ: 0909 123 456 | Website: www.yenbinh.vn
        </td>
    </tr>
    
    <!-- Người lập hóa đơn -->
    <tr>
        <td colspan="2" style="padding-top: 30px; text-align: left; font-size: 12px;">
            Người lập hóa đơn:<br>
            <strong>{{ $invoice->updatedBy->name ?? 'Nguyễn Văn A' }}</strong>
        </td>
        <td colspan="2" style="padding-top: 30px; text-align: right; font-size: 12px;">
            Ngày in: {{ now()->format('d/m/Y') }}
        </td>
    </tr>
</table>