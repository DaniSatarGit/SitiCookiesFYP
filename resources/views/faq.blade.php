@extends('layouts.site', ['adminLayout' => $admin ?? false])

@section('title', 'Siti Cookies - FAQ')

@section('content')
    <main class="container">
        <section>
            <h2 class="center">Frequently Asked Questions</h2>
            @foreach ([
                ['How to order online?', 'You can place an order by selecting the desired cookies, adding them to your cart, and following the checkout steps on our website.'],
                ['Does Siti Cookies offer nationwide delivery?', 'Yes, we offer nationwide delivery in Malaysia using trusted courier services.'],
                ['How long is the delivery time?', 'Delivery typically takes 3-5 business days, depending on your location.'],
                ['Can I track my order?', 'Ya, selepas pesanan dihantar, anda akan menerima nombor penjejakan yang boleh digunakan untuk menjejak status penghantaran anda.'],
                ['Are there any discounts or special promotions?', 'Yes, we frequently offer special promotions and discounts. Please visit our website or follow us on social media for the latest information.'],
                ['What if I have a food allergy?', 'We recommend carefully reading the product descriptions or contacting us for more information about the ingredients used in our cookies.'],
                ['How do I contact Siti Cookies customer service?', 'You can contact us through the contact form on our website or via email at support@siticookies.com.'],
                ['Can I place a custom order or customized cookies?', 'Yes, we offer custom order services for special events. Please contact us for further discussion regarding your requirements.'],
                ['What payment methods are accepted?', 'We accept payments through Cash on Delivery, debit cards, and online bank transfers.'],
                ['What is the return or refund policy?', 'We take customer satisfaction seriously. If you encounter any problems with your order, please contact us within 7 days of the receipt date for resolution.'],
            ] as [$question, $answer])
                <article class="card center" style="background:#f1e8da;margin-bottom:15px;">
                    <h3>{{ $question }}</h3>
                    <p>{{ $answer }}</p>
                </article>
            @endforeach
        </section>
    </main>
@endsection
