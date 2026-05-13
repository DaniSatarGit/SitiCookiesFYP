@extends('layouts.site', ['active' => 'cart'])

@section('title', 'Siti Cookies Online Banking')

@section('content')
    <main class="container">
        <section class="card">
            <h2>Online Banking Payment Instructions</h2>
            <ol>
                <li>Log in to your online banking account through your bank's official website or mobile app.</li>
                <li>Navigate to the Transfer or Payments section.</li>
                <li>Transfer to Maybank account 123456789 under Siti Cookies Shop.</li>
                <li>Double-check the entered details before confirming payment.</li>
                <li>Save a clear screenshot or PDF copy of your transfer receipt.</li>
            </ol>
            <p>Upload your receipt below after completing the transfer.</p>
        </section>

        <section class="card" style="margin-top:12px;">
            <form class="grid" action="{{ route('online-banking') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <label for="receipt">Upload Receipt:</label>
                <input type="file" name="receipt" id="receipt" accept=".jpg,.jpeg,.png,.pdf" required>
                <button type="submit" style="width:160px;">Upload</button>
            </form>
        </section>
    </main>
@endsection
