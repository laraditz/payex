<?php

use Carbon\Carbon;

?>
@extends('payex::layouts.app')

@section('content')
<section class="text-gray-600 body-font">
    <div class="container mx-auto flex px-5 py-24 items-center justify-center flex-col">

        @if($status_code==11)
        <svg xmlns="http://www.w3.org/2000/svg" class="w-1/4 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        @elseif($status_code==88)
        <svg xmlns="http://www.w3.org/2000/svg" class="w-1/4 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
        </svg>
        @elseif($status_code==66)
        <svg xmlns="http://www.w3.org/2000/svg" class="w-1/4 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        @endif

        <div class="text-center lg:w-2/3 w-full">

            <h1 class="title-font sm:text-2xl text-xl mx-4 font-medium text-gray-900">
                Payment is {{ $status_text }}
            </h1>

            @if($description)
            <h3 class="text-lg font-medium">{{ $description }}</h3>
            @endif

            @if($payment)
            <div class="container py-2 mx-auto mt-2">

                <div class="w-full">
                    <div class="mt-4">
                        @if($payment->ref_no)
                        <div class="flex flex-col sm:flex-row justify-between items-center sm:items-start py-3 border-t border-gray-300 last:border-none">
                            <span class="w-full sm:w-1/3 font-medium text-center sm:text-left">Ref No</span>
                            <span class="flex-1 text-center sm:text-left">{{ $payment->ref_no }}</span>
                        </div>
                        @endif

                        <div class="flex flex-col sm:flex-row justify-between items-center sm:items-start py-3 border-t border-gray-300 last:border-none">
                            <span class="w-full sm:w-1/3 font-medium text-center sm:text-left">Paid Amount</span>
                            <span class="flex-1 text-center sm:text-left">{{ $payment->currency_code }} {{ number_format($payment->amount/100, 2) ?? '-' }}</span>
                        </div>
                        <div class="flex flex-col sm:flex-row justify-between items-center sm:items-start py-3 border-t border-gray-300 ">
                            <span class="w-full sm:w-1/3 font-medium text-center sm:text-left">Date</span>
                            <span class="flex-1 text-center sm:text-left">{{ Carbon::parse($payment->created_at)->format('d/m/Y H:i:s') }}</span>
                        </div>
                    </div>
                </div>
            </div>

              <div class="flex justify-center mt-4">
                <form method="POST" action="{{ $payment->return_url }}" id="form-complete">
                @if($data)
                @foreach($data as $name=>$value)
                <input type="hidden" name="{{ $name }}" value="{{ $value }}" class="form-control mt-4
                    block
                    w-full
                    px-3
                    py-1.5
                    text-base
                    font-normal
                    text-gray-700
                    bg-white bg-clip-padding
                    border border-solid border-gray-300
                    rounded
                    transition
                    ease-in-out
                    m-0
                focus:text-gray-700 focus:bg-white focus:border-blue-600 focus:outline-none"
                />
                @endforeach
                @endif
                    <button id="btnReturn" type="submit" class="inline-flex px-6 py-2.5 bg-blue-600 text-white font-medium text-md leading-tight uppercase rounded shadow-md hover:bg-blue-700 hover:shadow-lg focus:bg-blue-700 focus:shadow-lg focus:outline-none focus:ring-0 active:bg-blue-800 active:shadow-lg transition duration-150 ease-in-out" >
                    Return <span id="countdown" class="ml-2">(10)</span>
                    </button>
                </form>
            </div>
            @endif

        </div>
    </div>
</section>
<script>
    var timeleft = 10;
    var downloadTimer = setInterval(function(){
    if(timeleft <= 0){
        clearInterval(downloadTimer);
        document.getElementById("countdown").innerHTML = "Finished";
        document.getElementById("btnReturn").innerHTML = "Redirecting...";
    } else {
        document.getElementById("countdown").innerHTML = `(${timeleft})` ;
    }
    timeleft -= 1;
    }, 1000);

     setTimeout(()=>document.getElementById("form-complete").submit(), 11000);
</script>
@endsection
