<!-- resources/views/exports/invoice.blade.php -->

<table style="width: 100%; font-family: 'Arial', sans-serif; border-collapse: collapse;">
    <!-- Logo và thông tin -->
    <tr>
        <td colspan="4" style="height: 80px; text-align: center;">
            <img src="{{ public_path('images/logo.png') }}" alt="Logo" style="height: 60px;">
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
                SỐ CĂN HỘ: <strong>{{ $invoice->apartment->apartment_number ?? '----' }}</strong>
            </span>
        </td>
    </tr>
    <tr>
        <td colspan="4" style="padding: 5px 0;">
            Ngày phát hành: <strong>{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d/m/Y') }}</strong>
            <span style="margin-right: 30px;"> - </span>
            Hạn thanh toán: <strong style="color: #C55A11;">{{ \Carbon\Carbon::parse($invoice->due_date)->format('d/m/Y') }}</strong>
        </td>
    </tr>

    <!-- Người lập hóa đơn & Ngày in -->
    <tr>
        <td colspan="2" style="padding: 5px 0; text-align: left;">
            Người lập hóa đơn: <strong>{{ $invoice->updatedBy->name ?? '----' }}</strong>
        </td>
    </tr>

    <tr>
        <td colspan="2" style="padding: 5px 0; text-align: left;">
            Ngày in: <strong>{{ now()->format('d/m/Y') }}</strong>
        </td>
    </tr>

    <!-- Khoảng trống -->
    <tr>
        <td colspan="4" style="height: 10px;"></td>
    </tr>

    <!-- Header bảng -->
    <tr>
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
    <tr style="background-color: #D9E1F2;">
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

    <!-- Thông tin thanh toán với QR Code -->
    <tr style="height: 100px">
        <!-- Thông tin thanh toán -->
        <td colspan="3" style="padding: 15px 20px; background-color: #D9E1F2; font-size: 14px; border-radius: 10px 0 0 10px; vertical-align: middle;">
            <div style="line-height: 1.1;">
                <strong style="font-size: 15px;">Thông tin thanh toán:</strong><br>
                Ngân hàng: <strong>VIETCOMBANK</strong><br>
                STK: <strong>1234567890</strong><br>
                Chủ TK: <strong>CTY YÊN BÌNH</strong><br>
                Nội dung:
                <strong style="color: #C00000;">
                    {{ $invoice->apartment->apartment_number ?? 'MÃ CĂN HỘ' }} THANH TOAN HOA DON {{ \Carbon\Carbon::parse($invoice->invoice_date)->format('m/Y') }}
                </strong>
            </div>
        </td>
        <!-- Mã QR -->
        <td style="padding: 10px; background-color: #D9E1F2; text-align: center; vertical-align: middle; width: 120px; border-radius: 0 10px 10px 0;">
            <div style="border: 1px dashed #999; padding: 5px; background-color: white;">
                <!-- QR code hiển thị tại đây -->
                <img src="{{ $qrCodeUrl ?? '' }}" alt="QR Code" style="max-width: 100%; height: auto;">
            </div>
            <div style="font-size: 11px; margin-top: 5px;">Quét để thanh toán</div>
        </td>
    </tr>

    <!-- TRƯỞNG BAN QUẢN LÝ -->
    <tr>
        <td colspan="2"></td>
        <td colspan="2" style="padding: 10px; text-align: center; font-size: 13px; font-weight: bold; vertical-align: top;">
            <!-- Thay đổi từ right sang center -->
            TRƯỞNG BAN QUẢN LÝ
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
</table>