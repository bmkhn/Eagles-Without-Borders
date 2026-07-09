<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', "Eagles Without Borders") }}</title>

        <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:300,400,500,600,700,800&display=swap" rel="stylesheet" />

        <style>
            @font-face {
                font-family: 'Brush Script';
                src: url('/fonts/BrushScriptOpti-Regular.otf') format('opentype');
                font-weight: normal;
                font-style: normal;
                font-display: swap;
            }
        </style>

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            <style>
                /*! tailwindcss v4.0.7 | MIT License | https://tailwindcss.com */@layer theme{:root,:host{--font-sans:'Figtree',ui-sans-serif,system-ui,sans-serif,"Apple Color Emoji","Segoe UI Emoji","Segoe UI Symbol","Noto Color Emoji";--font-serif:ui-serif,Georgia,Cambria,"Times New Roman",Times,serif;--font-mono:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,"Liberation Mono","Courier New",monospace;--color-red-50:oklch(.971 .013 17.38);--color-red-100:oklch(.936 .032 17.717);--color-red-200:oklch(.885 .062 18.334);--color-red-300:oklch(.808 .114 19.571);--color-red-400:oklch(.704 .191 22.216);--color-red-500:oklch(.637 .237 25.331);--color-red-600:oklch(.577 .245 27.325);--color-red-700:oklch(.505 .213 27.518);--color-red-800:oklch(.444 .177 26.899);--color-red-900:oklch(.396 .141 25.723);--color-red-950:oklch(.258 .092 26.042);--color-amber-50:oklch(.987 .022 95.277);--color-amber-100:oklch(.962 .059 95.617);--color-amber-200:oklch(.924 .12 95.746);--color-amber-300:oklch(.879 .169 91.605);--color-amber-400:oklch(.828 .189 84.429);--color-amber-500:oklch(.769 .188 70.08);--color-amber-600:oklch(.666 .179 58.318);--color-amber-700:oklch(.555 .163 48.998);--color-amber-800:oklch(.473 .137 46.201);--color-amber-900:oklch(.414 .112 45.904);--color-amber-950:oklch(.279 .077 45.635);--color-yellow-50:oklch(.987 .026 102.212);--color-yellow-100:oklch(.973 .071 103.193);--color-yellow-200:oklch(.945 .129 101.54);--color-yellow-300:oklch(.905 .182 98.111);--color-yellow-400:oklch(.852 .199 91.936);--color-yellow-500:oklch(.795 .184 86.047);--color-yellow-600:oklch(.681 .162 75.834);--color-yellow-700:oklch(.554 .135 66.442);--color-yellow-800:oklch(.476 .114 61.907);--color-yellow-900:oklch(.421 .095 57.708);--color-yellow-950:oklch(.286 .066 53.813);--color-green-50:oklch(.982 .018 155.826);--color-green-100:oklch(.962 .044 156.743);--color-green-200:oklch(.925 .084 155.995);--color-green-300:oklch(.871 .15 154.449);--color-green-400:oklch(.792 .209 151.711);--color-green-500:oklch(.723 .219 149.579);--color-green-600:oklch(.627 .194 149.214);--color-green-700:oklch(.527 .154 150.069);--color-green-800:oklch(.448 .119 151.328);--color-green-900:oklch(.393 .095 152.535);--color-green-950:oklch(.266 .065 152.934);--color-sky-50:oklch(.977 .013 236.62);--color-sky-100:oklch(.951 .026 236.824);--color-sky-200:oklch(.901 .058 230.902);--color-sky-300:oklch(.828 .111 230.318);--color-sky-400:oklch(.746 .16 232.661);--color-sky-500:oklch(.685 .169 237.323);--color-sky-600:oklch(.588 .158 241.966);--color-sky-700:oklch(.5 .134 242.749);--color-sky-800:oklch(.443 .11 240.79);--color-sky-900:oklch(.391 .09 240.876);--color-sky-950:oklch(.293 .066 243.157);--color-blue-50:oklch(.97 .014 254.604);--color-blue-100:oklch(.932 .032 255.585);--color-blue-200:oklch(.882 .059 254.128);--color-blue-300:oklch(.809 .105 251.813);--color-blue-400:oklch(.707 .165 254.624);--color-blue-500:oklch(.623 .214 259.815);--color-blue-600:oklch(.546 .245 262.881);--color-blue-700:oklch(.488 .243 264.376);--color-blue-800:oklch(.424 .199 265.638);--color-blue-900:oklch(.379 .146 265.522);--color-blue-950:oklch(.282 .091 267.935);--color-indigo-50:oklch(.962 .018 272.314);--color-indigo-100:oklch(.93 .034 272.788);--color-indigo-200:oklch(.87 .065 274.039);--color-indigo-300:oklch(.785 .115 274.713);--color-indigo-400:oklch(.673 .182 276.935);--color-indigo-500:oklch(.585 .233 277.117);--color-indigo-600:oklch(.511 .262 276.966);--color-indigo-700:oklch(.457 .24 277.023);--color-indigo-800:oklch(.398 .195 277.366);--color-indigo-900:oklch(.359 .144 278.697);--color-indigo-950:oklch(.257 .09 281.288);--color-violet-50:oklch(.969 .016 293.756);--color-violet-100:oklch(.943 .029 294.588);--color-violet-200:oklch(.894 .057 293.283);--color-violet-300:oklch(.811 .111 293.571);--color-violet-400:oklch(.702 .183 293.541);--color-violet-500:oklch(.606 .25 292.717);--color-violet-600:oklch(.541 .281 293.009);--color-violet-700:oklch(.491 .27 292.581);--color-violet-800:oklch(.432 .232 292.759);--color-violet-900:oklch(.38 .189 293.745);--color-violet-950:oklch(.283 .141 291.089);--color-purple-50:oklch(.977 .014 308.299);--color-purple-100:oklch(.946 .033 307.174);--color-purple-200:oklch(.902 .063 306.703);--color-purple-300:oklch(.827 .119 306.383);--color-purple-400:oklch(.714 .203 305.504);--color-purple-500:oklch(.627 .265 303.9);--color-purple-600:oklch(.558 .288 302.321);--color-purple-700:oklch(.496 .265 301.924);--color-purple-800:oklch(.438 .218 303.724);--color-purple-900:oklch(.381 .176 304.987);--color-purple-950:oklch(.291 .149 302.717);--color-gray-50:oklch(.985 .002 247.839);--color-gray-100:oklch(.967 .003 264.542);--color-gray-200:oklch(.928 .006 264.531);--color-gray-300:oklch(.872 .01 258.338);--color-gray-400:oklch(.707 .022 261.325);--color-gray-500:oklch(.551 .027 264.364);--color-gray-600:oklch(.446 .03 256.802);--color-gray-700:oklch(.373 .034 259.733);--color-gray-800:oklch(.278 .033 256.848);--color-gray-900:oklch(.21 .034 264.665);--color-gray-950:oklch(.13 .028 261.692);--color-zinc-50:oklch(.985 0 0);--color-zinc-100:oklch(.967 .001 286.375);--color-zinc-200:oklch(.92 .004 286.32);--color-zinc-300:oklch(.871 .006 286.286);--color-zinc-400:oklch(.705 .015 286.067);--color-zinc-500:oklch(.552 .016 285.938);--color-zinc-600:oklch(.442 .017 285.786);--color-zinc-700:oklch(.37 .013 285.805);--color-zinc-800:oklch(.274 .006 286.033);--color-zinc-900:oklch(.21 .006 285.885);--color-zinc-950:oklch(.141 .005 285.823);--color-stone-50:oklch(.985 .001 106.423);--color-stone-100:oklch(.97 .001 106.424);--color-stone-200:oklch(.923 .003 48.717);--color-stone-300:oklch(.869 .005 56.366);--color-stone-400:oklch(.709 .01 56.259);--color-stone-500:oklch(.553 .013 58.071);--color-stone-600:oklch(.444 .011 73.639);--color-stone-700:oklch(.374 .01 67.558);--color-stone-800:oklch(.268 .007 34.298);--color-stone-900:oklch(.216 .006 56.043);--color-stone-950:oklch(.147 .004 49.25);--color-black:#000;--color-white:#fff;--spacing:.25rem;--breakpoint-sm:40rem;--breakpoint-md:48rem;--breakpoint-lg:64rem;--breakpoint-xl:80rem;--breakpoint-2xl:96rem;--container-xs:20rem;--container-sm:24rem;--container-md:28rem;--container-lg:32rem;--container-xl:36rem;--container-2xl:42rem;--container-3xl:48rem;--container-4xl:56rem;--container-5xl:64rem;--container-6xl:72rem;--container-7xl:80rem;--text-xs:.75rem;--text-xs--line-height:calc(1/.75);--text-sm:.875rem;--text-sm--line-height:calc(1.25/.875);--text-base:1rem;--text-base--line-height:1.5;--text-lg:1.125rem;--text-lg--line-height:calc(1.75/1.125);--text-xl:1.25rem;--text-xl--line-height:calc(1.75/1.25);--text-2xl:1.5rem;--text-2xl--line-height:calc(2/1.5);--text-3xl:1.875rem;--text-3xl--line-height:1.2;--text-4xl:2.25rem;--text-4xl--line-height:calc(2.5/2.25);--text-5xl:3rem;--text-5xl--line-height:1;--text-6xl:3.75rem;--text-6xl--line-height:1;--text-7xl:4.5rem;--text-7xl--line-height:1;--text-8xl:6rem;--text-8xl--line-height:1;--text-9xl:8rem;--text-9xl--line-height:1;--font-weight-thin:100;--font-weight-extralight:200;--font-weight-light:300;--font-weight-normal:400;--font-weight-medium:500;--font-weight-semibold:600;--font-weight-bold:700;--font-weight-extrabold:800;--font-weight-black:900;--tracking-tighter:-.05em;--tracking-tight:-.025em;--tracking-normal:0em;--tracking-wide:.025em;--tracking-wider:.05em;--tracking-widest:.1em;--leading-tight:1.25;--leading-snug:1.375;--leading-normal:1.5;--leading-relaxed:1.625;--leading-loose:2;--radius-xs:.125rem;--radius-sm:.25rem;--radius-md:.375rem;--radius-lg:.5rem;--radius-xl:.75rem;--radius-2xl:1rem;--radius-3xl:1.5rem;--radius-4xl:2rem;--shadow-xs:0 1px 2px 0 #0000000d;--shadow-sm:0 1px 3px 0 #0000001a,0 1px 2px -1px #0000001a;--shadow-md:0 4px 6px -1px #0000001a,0 2px 4px -2px #0000001a;--shadow-lg:0 10px 15px -3px #0000001a,0 4px 6px -4px #0000001a;--shadow-xl:0 20px 25px -5px #0000001a,0 8px 10px -6px #0000001a;--shadow-2xl:0 25px 50px -12px #00000040;--drop-shadow-xs:0 1px 1px #0000000d;--drop-shadow-sm:0 1px 2px #00000026;--drop-shadow-md:0 3px 3px #0000001f;--drop-shadow-lg:0 4px 4px #00000026;--drop-shadow-xl:0 9px 7px #0000001a;--drop-shadow-2xl:0 25px 25px #00000026;--ease-in:cubic-bezier(.4,0,1,1);--ease-out:cubic-bezier(0,0,.2,1);--ease-in-out:cubic-bezier(.4,0,.2,1);--animate-spin:spin 1s linear infinite;--animate-pulse:pulse 2s cubic-bezier(.4,0,.6,1)infinite;--blur-sm:8px;--blur-md:12px;--blur-lg:16px;--blur-xl:24px;--blur-2xl:40px;--blur-3xl:64px;--aspect-video:16/9;--default-transition-duration:.15s;--default-transition-timing-function:cubic-bezier(.4,0,.2,1);--default-font-family:var(--font-sans);--default-font-feature-settings:var(--font-sans--font-feature-settings);--default-font-variation-settings:var(--font-sans--font-variation-settings);--default-mono-font-family:var(--font-mono);--default-mono-font-feature-settings:var(--font-mono--font-feature-settings);--default-mono-font-variation-settings:var(--font-mono--font-variation-settings)}}@layer base{*,:after,:before,::backdrop{box-sizing:border-box;border:0 solid;margin:0;padding:0}::file-selector-button{box-sizing:border-box;border:0 solid;margin:0;padding:0}html,:host{-webkit-text-size-adjust:100%;-moz-tab-size:4;tab-size:4;line-height:1.5;font-family:var(--default-font-family,ui-sans-serif,system-ui,sans-serif,"Apple Color Emoji","Segoe UI Emoji","Segoe UI Symbol","Noto Color Emoji");font-feature-settings:var(--default-font-feature-settings,normal);font-variation-settings:var(--default-font-variation-settings,normal);-webkit-tap-highlight-color:transparent}body{line-height:inherit}hr{height:0;color:inherit;border-top-width:1px}h1,h2,h3,h4,h5,h6{font-size:inherit;font-weight:inherit}a{color:inherit;-webkit-text-decoration:inherit;text-decoration:inherit}b,strong{font-weight:bolder}table{text-indent:0;border-color:inherit;border-collapse:collapse}button,input,select,optgroup,textarea{font:inherit;font-feature-settings:inherit;font-variation-settings:inherit;letter-spacing:inherit;color:inherit;opacity:1;background-color:#0000;border-radius:0}::file-selector-button{font:inherit;font-feature-settings:inherit;font-variation-settings:inherit;letter-spacing:inherit;color:inherit;opacity:1;background-color:#0000;border-radius:0}::placeholder{opacity:1;color:color-mix(in oklab,currentColor 50%,transparent)}textarea{resize:vertical}img,video{max-width:100%;height:auto}img,video,svg,canvas,audio,iframe,embed,object{vertical-align:middle;display:block}*{scrollbar-width:thin;scrollbar-color:var(--color-amber-600) transparent}}@layer components;@layer utilities{.absolute{position:absolute}.fixed{position:fixed}.relative{position:relative}.sticky{position:sticky}.inset-0{inset:calc(var(--spacing)*0)}.-inset-x-4{inset-inline:calc(var(--spacing)*-4)}.top-0{top:calc(var(--spacing)*0)}.top-1\\/2{top:50%}.top-\\[1px\\]{top:1px}.right-0{right:calc(var(--spacing)*0)}.right-2{right:calc(var(--spacing)*2)}.right-4{right:calc(var(--spacing)*4)}.bottom-0{bottom:calc(var(--spacing)*0)}.left-0{left:calc(var(--spacing)*0)}.left-2{left:calc(var(--spacing)*2)}.left-1\\/2{left:50%}.isolate{isolation:isolate}.-z-10{z-index:-10}.z-10{z-index:10}.z-20{z-index:20}.z-30{z-index:30}.z-50{z-index:50}.col-span-full{grid-column:1/-1}.col-start-1{grid-column-start:1}.col-start-2{grid-column-start:2}.col-end-2{grid-column-end:2}.col-end-3{grid-column-end:3}.row-start-1{grid-row-start:1}.row-end-2{grid-row-end:2}.mx-auto{margin-inline:auto}.my-8{margin-block:calc(var(--spacing)*8)}.my-16{margin-block:calc(var(--spacing)*16)}.my-20{margin-block:calc(var(--spacing)*20)}.-mt-1{margin-top:calc(var(--spacing)*-1)}.mt-1{margin-top:calc(var(--spacing)*1)}.mt-2{margin-top:calc(var(--spacing)*2)}.mt-3{margin-top:calc(var(--spacing)*3)}.mt-4{margin-top:calc(var(--spacing)*4)}.mt-6{margin-top:calc(var(--spacing)*6)}.mt-8{margin-top:calc(var(--spacing)*8)}.mt-10{margin-top:calc(var(--spacing)*10)}.mt-12{margin-top:calc(var(--spacing)*12)}.mt-16{margin-top:calc(var(--spacing)*16)}.mt-20{margin-top:calc(var(--spacing)*20)}.mr-1{margin-right:calc(var(--spacing)*1)}.mr-2{margin-right:calc(var(--spacing)*2)}.mb-1{margin-bottom:calc(var(--spacing)*1)}.mb-2{margin-bottom:calc(var(--spacing)*2)}.mb-3{margin-bottom:calc(var(--spacing)*3)}.mb-4{margin-bottom:calc(var(--spacing)*4)}.mb-6{margin-bottom:calc(var(--spacing)*6)}.mb-8{margin-bottom:calc(var(--spacing)*8)}.mb-10{margin-bottom:calc(var(--spacing)*10)}.mb-12{margin-bottom:calc(var(--spacing)*12)}.mb-16{margin-bottom:calc(var(--spacing)*16)}.ml-2{margin-left:calc(var(--spacing)*2)}.ml-4{margin-left:calc(var(--spacing)*4)}.ml-auto{margin-left:auto}.-translate-x-1\\/2{--tw-translate-x:calc(calc(1/2*100%)*-1);translate:var(--tw-translate-x)var(--tw-translate-y)}.-translate-y-1\\/2{--tw-translate-y:calc(calc(1/2*100%)*-1);translate:var(--tw-translate-x)var(--tw-translate-y)}.transform{transform:var(--tw-rotate-x)var(--tw-rotate-y)var(--tw-rotate-z)var(--tw-skew-x)var(--tw-skew-y)}.block{display:block}.flex{display:flex}.grid{display:grid}.hidden{display:none}.inline-block{display:inline-block}.inline-flex{display:inline-flex}.aspect-\\[2\\/1\\]{aspect-ratio:2/1}.size-3{width:calc(var(--spacing)*3);height:calc(var(--spacing)*3)}.size-4{width:calc(var(--spacing)*4);height:calc(var(--spacing)*4)}.size-5{width:calc(var(--spacing)*5);height:calc(var(--spacing)*5)}.size-6{width:calc(var(--spacing)*6);height:calc(var(--spacing)*6)}.size-7{width:calc(var(--spacing)*7);height:calc(var(--spacing)*7)}.size-8{width:calc(var(--spacing)*8);height:calc(var(--spacing)*8)}.size-10{width:calc(var(--spacing)*10);height:calc(var(--spacing)*10)}.size-12{width:calc(var(--spacing)*12);height:calc(var(--spacing)*12)}.size-14{width:calc(var(--spacing)*14);height:calc(var(--spacing)*14)}.size-16{width:calc(var(--spacing)*16);height:calc(var(--spacing)*16)}.size-20{width:calc(var(--spacing)*20);height:calc(var(--spacing)*20)}.h-0\\.5{height:calc(var(--spacing)*0.5)}.h-1{height:calc(var(--spacing)*1)}.h-6{height:calc(var(--spacing)*6)}.h-8{height:calc(var(--spacing)*8)}.h-16{height:calc(var(--spacing)*16)}.h-24{height:calc(var(--spacing)*24)}.h-32{height:calc(var(--spacing)*32)}.h-64{height:calc(var(--spacing)*64)}.h-auto{height:auto}.h-full{height:100%}.min-h-\\[60vh\\]{min-height:60vh}.min-h-screen{min-height:100vh}.w-0\\.5{width:calc(var(--spacing)*0.5)}.w-4{width:calc(var(--spacing)*4)}.w-6{width:calc(var(--spacing)*6)}.w-8{width:calc(var(--spacing)*8)}.w-20{width:calc(var(--spacing)*20)}.w-24{width:calc(var(--spacing)*24)}.w-32{width:calc(var(--spacing)*32)}.w-full{width:100%}.max-w-2xl{max-width:var(--container-2xl)}.max-w-3xl{max-width:var(--container-3xl)}.max-w-4xl{max-width:var(--container-4xl)}.max-w-5xl{max-width:var(--container-5xl)}.max-w-6xl{max-width:var(--container-6xl)}.max-w-7xl{max-width:var(--container-7xl)}.max-w-lg{max-width:var(--container-lg)}.max-w-md{max-width:var(--container-md)}.max-w-xl{max-width:var(--container-xl)}.max-w-xs{max-width:var(--container-xs)}.flex-1{flex:1}.flex-shrink-0{flex-shrink:0}.shrink-0{flex-shrink:0}.origin-top{transform-origin:top}.origin-top-left{transform-origin:top left}.-rotate-3{rotate:-3deg}.rotate-12{rotate:12deg}.scale-125{--tw-scale-x:125%;--tw-scale-y:125%;scale:var(--tw-scale-x)var(--tw-scale-y)}.scale-x-150{--tw-scale-x:150%;scale:var(--tw-scale-x)var(--tw-scale-y)}.transform-gpu{transform:translateZ(0)var(--tw-rotate-x)var(--tw-rotate-y)var(--tw-rotate-z)var(--tw-skew-x)var(--tw-skew-y)}.scroll-mt-24{scroll-margin-top:calc(var(--spacing)*24)}.flex-col{flex-direction:column}.flex-col-reverse{flex-direction:column-reverse}.flex-wrap{flex-wrap:wrap}.items-center{align-items:center}.items-end{align-items:flex-end}.items-start{align-items:flex-start}.justify-between{justify-content:space-between}.justify-center{justify-content:center}.gap-1{gap:calc(var(--spacing)*1)}.gap-2{gap:calc(var(--spacing)*2)}.gap-3{gap:calc(var(--spacing)*3)}.gap-4{gap:calc(var(--spacing)*4)}.gap-5{gap:calc(var(--spacing)*5)}.gap-6{gap:calc(var(--spacing)*6)}.gap-8{gap:calc(var(--spacing)*8)}.gap-10{gap:calc(var(--spacing)*10)}.gap-12{gap:calc(var(--spacing)*12)}.gap-x-6{column-gap:calc(var(--spacing)*6)}.gap-x-8{column-gap:calc(var(--spacing)*8)}.gap-y-10{row-gap:calc(var(--spacing)*10)}.gap-y-12{row-gap:calc(var(--spacing)*12)}.space-y-1>:not(:last-child){--tw-space-y-reverse:0;margin-block-start:calc(calc(var(--spacing)*1)*var(--tw-space-y-reverse));margin-block-end:calc(calc(var(--spacing)*1)*calc(1 - var(--tw-space-y-reverse)))}.space-y-2>:not(:last-child){--tw-space-y-reverse:0;margin-block-start:calc(calc(var(--spacing)*2)*var(--tw-space-y-reverse));margin-block-end:calc(calc(var(--spacing)*2)*calc(1 - var(--tw-space-y-reverse)))}.space-y-3>:not(:last-child){--tw-space-y-reverse:0;margin-block-start:calc(calc(var(--spacing)*3)*var(--tw-space-y-reverse));margin-block-end:calc(calc(var(--spacing)*3)*calc(1 - var(--tw-space-y-reverse)))}.space-y-4>:not(:last-child){--tw-space-y-reverse:0;margin-block-start:calc(calc(var(--spacing)*4)*var(--tw-space-y-reverse));margin-block-end:calc(calc(var(--spacing)*4)*calc(1 - var(--tw-space-y-reverse)))}.divide-y>:not(:last-child){--tw-divide-y-reverse:0;border-bottom-style:var(--tw-border-style);border-top-style:var(--tw-border-style);border-top-width:calc(1px*var(--tw-divide-y-reverse));border-bottom-width:calc(1px*calc(1 - var(--tw-divide-y-reverse)))}.divide-amber-800>:not(:last-child){border-color:var(--color-amber-800)}.divide-gray-800>:not(:last-child){border-color:var(--color-gray-800)}.overflow-hidden{overflow:hidden}.overflow-visible{overflow:visible}.rounded-2xl{border-radius:var(--radius-2xl)}.rounded-3xl{border-radius:var(--radius-3xl)}.rounded-full{border-radius:3.40282e38px}.rounded-lg{border-radius:var(--radius-lg)}.rounded-md{border-radius:var(--radius-md)}.rounded-xl{border-radius:var(--radius-xl)}.border{border-style:var(--tw-border-style);border-width:1px}.border-2{border-style:var(--tw-border-style);border-width:2px}.border-t{border-top-style:var(--tw-border-style);border-top-width:1px}.border-b{border-bottom-style:var(--tw-border-style);border-bottom-width:1px}.border-black\\/10{border-color:color-mix(in oklch,var(--color-black)10%,transparent)}.border-amber-200{border-color:var(--color-amber-200)}.border-amber-400\\/20{border-color:color-mix(in oklch,var(--color-amber-400)20%,transparent)}.border-amber-500{border-color:var(--color-amber-500)}.border-amber-700{border-color:var(--color-amber-700)}.border-amber-800{border-color:var(--color-amber-800)}.border-gray-200{border-color:var(--color-gray-200)}.border-gray-300{border-color:var(--color-gray-300)}.border-gray-700{border-color:var(--color-gray-700)}.border-gray-800{border-color:var(--color-gray-800)}.border-transparent{border-color:#0000}.border-white{border-color:var(--color-white)}.border-white\\/20{border-color:color-mix(in oklch,var(--color-white)20%,transparent)}.border-white\\/30{border-color:color-mix(in oklch,var(--color-white)30%,transparent)}.bg-amber-50{background-color:var(--color-amber-50)}.bg-amber-100{background-color:var(--color-amber-100)}.bg-amber-400{background-color:var(--color-amber-400)}.bg-amber-500{background-color:var(--color-amber-500)}.bg-amber-500\\/10{background-color:color-mix(in oklch,var(--color-amber-500)10%,transparent)}.bg-amber-600{background-color:var(--color-amber-600)}.bg-amber-950{background-color:var(--color-amber-950)}.bg-black{background-color:var(--color-black)}.bg-black\\/20{background-color:color-mix(in oklch,var(--color-black)20%,transparent)}.bg-black\\/30{background-color:color-mix(in oklch,var(--color-black)30%,transparent)}.bg-black\\/40{background-color:color-mix(in oklch,var(--color-black)40%,transparent)}.bg-black\\/50{background-color:color-mix(in oklch,var(--color-black)50%,transparent)}.bg-gray-50{background-color:var(--color-gray-50)}.bg-gray-100{background-color:var(--color-gray-100)}.bg-gray-900{background-color:var(--color-gray-900)}.bg-gray-950{background-color:var(--color-gray-950)}.bg-indigo-600{background-color:var(--color-indigo-600)}.bg-sky-500{background-color:var(--color-sky-500)}.bg-sky-600{background-color:var(--color-sky-600)}.bg-white{background-color:var(--color-white)}.bg-white\\/5{background-color:color-mix(in oklch,var(--color-white)5%,transparent)}.bg-white\\/10{background-color:color-mix(in oklch,var(--color-white)10%,transparent)}.bg-gradient-to-b{--tw-gradient-position:to bottom in oklch;background-image:linear-gradient(var(--tw-gradient-stops))}.bg-gradient-to-br{--tw-gradient-position:to bottom right in oklch;background-image:linear-gradient(var(--tw-gradient-stops))}.bg-gradient-to-r{--tw-gradient-position:to right in oklch;background-image:linear-gradient(var(--tw-gradient-stops))}.bg-gradient-to-t{--tw-gradient-position:to top in oklch;background-image:linear-gradient(var(--tw-gradient-stops))}.from-amber-500{--tw-gradient-from:var(--color-amber-500);--tw-gradient-stops:var(--tw-gradient-via-stops,var(--tw-gradient-position,)var(--tw-gradient-from)var(--tw-gradient-from-position),var(--tw-gradient-to)var(--tw-gradient-to-position))}.from-amber-600{--tw-gradient-from:var(--color-amber-600);--tw-gradient-stops:var(--tw-gradient-via-stops,var(--tw-gradient-position,)var(--tw-gradient-from)var(--tw-gradient-from-position),var(--tw-gradient-to)var(--tw-gradient-to-position))}.from-black{--tw-gradient-from:var(--color-black);--tw-gradient-stops:var(--tw-gradient-via-stops,var(--tw-gradient-position,)var(--tw-gradient-from)var(--tw-gradient-from-position),var(--tw-gradient-to)var(--tw-gradient-to-position))}.from-gray-900{--tw-gradient-from:var(--color-gray-900);--tw-gradient-stops:var(--tw-gradient-via-stops,var(--tw-gradient-position,)var(--tw-gradient-from)var(--tw-gradient-from-position),var(--tw-gradient-to)var(--tw-gradient-to-position))}.from-gray-950{--tw-gradient-from:var(--color-gray-950);--tw-gradient-stops:var(--tw-gradient-via-stops,var(--tw-gradient-position,)var(--tw-gradient-from)var(--tw-gradient-from-position),var(--tw-gradient-to)var(--tw-gradient-to-position))}.via-amber-400{--tw-gradient-via:var(--color-amber-400);--tw-gradient-stops:var(--tw-gradient-via-stops,var(--tw-gradient-position,)var(--tw-gradient-from)var(--tw-gradient-from-position),var(--tw-gradient-via)var(--tw-gradient-via-position),var(--tw-gradient-to)var(--tw-gradient-to-position))}.via-gray-900{--tw-gradient-via:var(--color-gray-900);--tw-gradient-stops:var(--tw-gradient-via-stops,var(--tw-gradient-position,)var(--tw-gradient-from)var(--tw-gradient-from-position),var(--tw-gradient-via)var(--tw-gradient-via-position),var(--tw-gradient-to)var(--tw-gradient-to-position))}.to-amber-700{--tw-gradient-to:var(--color-amber-700);--tw-gradient-stops:var(--tw-gradient-via-stops,var(--tw-gradient-position,)var(--tw-gradient-from)var(--tw-gradient-from-position),var(--tw-gradient-to)var(--tw-gradient-to-position))}.to-black{--tw-gradient-to:var(--color-black);--tw-gradient-stops:var(--tw-gradient-via-stops,var(--tw-gradient-position,)var(--tw-gradient-from)var(--tw-gradient-from-position),var(--tw-gradient-to)var(--tw-gradient-to-position))}.to-gray-800{--tw-gradient-to:var(--color-gray-800);--tw-gradient-stops:var(--tw-gradient-via-stops,var(--tw-gradient-position,)var(--tw-gradient-from)var(--tw-gradient-from-position),var(--tw-gradient-to)var(--tw-gradient-to-position))}.to-indigo-700{--tw-gradient-to:var(--color-indigo-700);--tw-gradient-stops:var(--tw-gradient-via-stops,var(--tw-gradient-position,)var(--tw-gradient-from)var(--tw-gradient-from-position),var(--tw-gradient-to)var(--tw-gradient-to-position))}.to-sky-700{--tw-gradient-to:var(--color-sky-700);--tw-gradient-stops:var(--tw-gradient-via-stops,var(--tw-gradient-position,)var(--tw-gradient-from)var(--tw-gradient-from-position),var(--tw-gradient-to)var(--tw-gradient-to-position))}.to-transparent{--tw-gradient-to:transparent;--tw-gradient-stops:var(--tw-gradient-via-stops,var(--tw-gradient-position,)var(--tw-gradient-from)var(--tw-gradient-from-position),var(--tw-gradient-to)var(--tw-gradient-to-position))}.bg-clip-text{-webkit-background-clip:text;background-clip:text}.object-cover{object-fit:cover}.p-1{padding:calc(var(--spacing)*1)}.p-2{padding:calc(var(--spacing)*2)}.p-4{padding:calc(var(--spacing)*4)}.p-6{padding:calc(var(--spacing)*6)}.p-8{padding:calc(var(--spacing)*8)}.px-3{padding-inline:calc(var(--spacing)*3)}.px-4{padding-inline:calc(var(--spacing)*4)}.px-5{padding-inline:calc(var(--spacing)*5)}.px-6{padding-inline:calc(var(--spacing)*6)}.px-8{padding-inline:calc(var(--spacing)*8)}.py-1{padding-block:calc(var(--spacing)*1)}.py-2{padding-block:calc(var(--spacing)*2)}.py-2\\.5{padding-block:calc(var(--spacing)*2.5)}.py-3{padding-block:calc(var(--spacing)*3)}.py-4{padding-block:calc(var(--spacing)*4)}.py-6{padding-block:calc(var(--spacing)*6)}.py-8{padding-block:calc(var(--spacing)*8)}.py-12{padding-block:calc(var(--spacing)*12)}.py-16{padding-block:calc(var(--spacing)*16)}.py-20{padding-block:calc(var(--spacing)*20)}.py-24{padding-block:calc(var(--spacing)*24)}.pt-4{padding-top:calc(var(--spacing)*4)}.pt-6{padding-top:calc(var(--spacing)*6)}.pt-8{padding-top:calc(var(--spacing)*8)}.pt-12{padding-top:calc(var(--spacing)*12)}.pt-16{padding-top:calc(var(--spacing)*16)}.pt-20{padding-top:calc(var(--spacing)*20)}.pt-24{padding-top:calc(var(--spacing)*24)}.pb-4{padding-bottom:calc(var(--spacing)*4)}.pb-6{padding-bottom:calc(var(--spacing)*6)}.pb-12{padding-bottom:calc(var(--spacing)*12)}.pb-16{padding-bottom:calc(var(--spacing)*16)}.pb-20{padding-bottom:calc(var(--spacing)*20)}.pb-24{padding-bottom:calc(var(--spacing)*24)}.text-center{text-align:center}.text-right{text-align:right}.text-2xl{font-size:var(--text-2xl);line-height:var(--tw-leading,var(--text-2xl--line-height))}.text-3xl{font-size:var(--text-3xl);line-height:var(--tw-leading,var(--text-3xl--line-height))}.text-4xl{font-size:var(--text-4xl);line-height:var(--tw-leading,var(--text-4xl--line-height))}.text-5xl{font-size:var(--text-5xl);line-height:var(--tw-leading,var(--text-5xl--line-height))}.text-6xl{font-size:var(--text-6xl);line-height:var(--tw-leading,var(--text-6xl--line-height))}.text-base{font-size:var(--text-base);line-height:var(--tw-leading,var(--text-base--line-height))}.text-lg{font-size:var(--text-lg);line-height:var(--tw-leading,var(--text-lg--line-height))}.text-sm{font-size:var(--text-sm);line-height:var(--tw-leading,var(--text-sm--line-height))}.text-xl{font-size:var(--text-xl);line-height:var(--tw-leading,var(--text-xl--line-height))}.text-xs{font-size:var(--text-xs);line-height:var(--tw-leading,var(--text-xs--line-height))}.leading-relaxed{--tw-leading:var(--leading-relaxed);line-height:var(--leading-relaxed)}.leading-tight{--tw-leading:var(--leading-tight);line-height:var(--leading-tight)}.font-black{--tw-font-weight:var(--font-weight-black);font-weight:var(--font-weight-black)}.font-bold{--tw-font-weight:var(--font-weight-bold);font-weight:var(--font-weight-bold)}.font-extrabold{--tw-font-weight:var(--font-weight-extrabold);font-weight:var(--font-weight-extrabold)}.font-light{--tw-font-weight:var(--font-weight-light);font-weight:var(--font-weight-light)}.font-medium{--tw-font-weight:var(--font-weight-medium);font-weight:var(--font-weight-medium)}.font-semibold{--tw-font-weight:var(--font-weight-semibold);font-weight:var(--font-weight-semibold)}.tracking-tight{--tw-tracking:var(--tracking-tight);letter-spacing:var(--tracking-tight)}.tracking-wider{--tw-tracking:var(--tracking-wider);letter-spacing:var(--tracking-wider)}.text-amber-400{color:var(--color-amber-400)}.text-amber-500{color:var(--color-amber-500)}.text-amber-600{color:var(--color-amber-600)}.text-amber-700{color:var(--color-amber-700)}.text-amber-800{color:var(--color-amber-800)}.text-gray-300{color:var(--color-gray-300)}.text-gray-400{color:var(--color-gray-400)}.text-gray-500{color:var(--color-gray-500)}.text-gray-600{color:var(--color-gray-600)}.text-gray-700{color:var(--color-gray-700)}.text-gray-900{color:var(--color-gray-900)}.text-indigo-600{color:var(--color-indigo-600)}.text-sky-600{color:var(--color-sky-600)}.text-transparent{-webkit-text-fill-color:transparent;color:#0000}.text-white{color:var(--color-white)}.text-white\\/60{color:color-mix(in oklch,var(--color-white)60%,transparent)}.text-white\\/70{color:color-mix(in oklch,var(--color-white)70%,transparent)}.text-white\\/80{color:color-mix(in oklch,var(--color-white)80%,transparent)}.antialiased{-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale}.opacity-0{opacity:0}.opacity-10{opacity:.1}.opacity-20{opacity:.2}.opacity-30{opacity:.3}.opacity-40{opacity:.4}.opacity-50{opacity:.5}.opacity-60{opacity:.6}.opacity-70{opacity:.7}.shadow-2xl{--tw-shadow:0 25px 50px -12px var(--tw-shadow-color,#00000040);box-shadow:var(--tw-inset-shadow),var(--tw-inset-ring-shadow),var(--tw-ring-offset-shadow),var(--tw-ring-shadow),var(--tw-shadow)}.shadow-lg{--tw-shadow:0 10px 15px -3px var(--tw-shadow-color,#0000001a),0 4px 6px -4px var(--tw-shadow-color,#0000001a);box-shadow:var(--tw-inset-shadow),var(--tw-inset-ring-shadow),var(--tw-ring-offset-shadow),var(--tw-ring-shadow),var(--tw-shadow)}.shadow-xl{--tw-shadow:0 20px 25px -5px var(--tw-shadow-color,#0000001a),0 8px 10px -6px var(--tw-shadow-color,#0000001a);box-shadow:var(--tw-inset-shadow),var(--tw-inset-ring-shadow),var(--tw-ring-offset-shadow),var(--tw-ring-shadow),var(--tw-shadow)}.ring-1{--tw-ring-shadow:var(--tw-ring-inset,)0 0 0 calc(1px + var(--tw-ring-offset-width))var(--tw-ring-color,currentColor);box-shadow:var(--tw-inset-shadow),var(--tw-inset-ring-shadow),var(--tw-ring-offset-shadow),var(--tw-ring-shadow),var(--tw-shadow)}.ring-amber-400\\/20{--tw-ring-color:color-mix(in oklch,var(--color-amber-400)20%,transparent)}.ring-amber-500\\/20{--tw-ring-color:color-mix(in oklch,var(--color-amber-500)20%,transparent)}.ring-white\\/10{--tw-ring-color:color-mix(in oklch,var(--color-white)10%,transparent)}.ring-white\\/15{--tw-ring-color:color-mix(in oklch,var(--color-white)15%,transparent)}.blur-3xl{--tw-blur:blur(var(--blur-3xl));filter:var(--tw-blur,)var(--tw-brightness,)var(--tw-contrast,)var(--tw-grayscale,)var(--tw-hue-rotate,)var(--tw-invert,)var(--tw-saturate,)var(--tw-sepia,)var(--tw-drop-shadow,)}.blur-sm{--tw-blur:blur(var(--blur-sm));filter:var(--tw-blur,)var(--tw-brightness,)var(--tw-contrast,)var(--tw-grayscale,)var(--tw-hue-rotate,)var(--tw-invert,)var(--tw-saturate,)var(--tw-sepia,)var(--tw-drop-shadow,)}.grayscale{--tw-grayscale:grayscale(100%);filter:var(--tw-blur,)var(--tw-brightness,)var(--tw-contrast,)var(--tw-grayscale,)var(--tw-hue-rotate,)var(--tw-invert,)var(--tw-saturate,)var(--tw-sepia,)var(--tw-drop-shadow,)}.filter{filter:var(--tw-blur,)var(--tw-brightness,)var(--tw-contrast,)var(--tw-grayscale,)var(--tw-hue-rotate,)var(--tw-invert,)var(--tw-saturate,)var(--tw-sepia,)var(--tw-drop-shadow,)}.transition{transition-property:all;transition-timing-function:var(--tw-ease,var(--default-transition-timing-function));transition-duration:var(--tw-duration,var(--default-transition-duration))}.transition-all{transition-property:all;transition-timing-function:var(--tw-ease,var(--default-transition-timing-function));transition-duration:var(--tw-duration,var(--default-transition-duration))}.transition-colors{transition-property:color,background-color,border-color,text-decoration-color,fill,stroke;transition-timing-function:var(--tw-ease,var(--default-transition-timing-function));transition-duration:var(--tw-duration,var(--default-transition-duration))}.transition-shadow{transition-property:box-shadow;transition-timing-function:var(--tw-ease,var(--default-transition-timing-function));transition-duration:var(--tw-duration,var(--default-transition-duration))}.duration-200{--tw-duration:.2s;transition-duration:.2s}.duration-300{--tw-duration:.3s;transition-duration:.3s}.duration-500{--tw-duration:.5s;transition-duration:.5s}.ease-in-out{--tw-ease:var(--ease-in-out);transition-timing-function:var(--ease-in-out)}.ease-out{--tw-ease:var(--ease-out);transition-timing-function:var(--ease-out)}@media (hover:hover){.group\\/card:hover .group-hover\\/card\\:scale-100{--tw-scale-x:100%;--tw-scale-y:100%;scale:var(--tw-scale-x)var(--tw-scale-y)}.group\\/card:hover .group-hover\\/card\\:opacity-100{opacity:1}.hover\\:border-amber-400:hover{border-color:var(--color-amber-400)}.hover\\:border-amber-500:hover{border-color:var(--color-amber-500)}.hover\\:bg-amber-400:hover{background-color:var(--color-amber-400)}.hover\\:bg-amber-500:hover{background-color:var(--color-amber-500)}.hover\\:bg-amber-700:hover{background-color:var(--color-amber-700)}.hover\\:bg-white\\/20:hover{background-color:color-mix(in oklch,var(--color-white)20%,transparent)}.hover\\:text-amber-200:hover{color:var(--color-amber-200)}.hover\\:text-amber-500:hover{color:var(--color-amber-500)}.hover\\:text-amber-600:hover{color:var(--color-amber-600)}.hover\\:text-white:hover{color:var(--color-white)}.hover\\:ring-amber-500\\/40:hover{--tw-ring-color:color-mix(in oklch,var(--color-amber-500)40%,transparent)}.hover\\:shadow-amber-500\\/25:hover{--tw-shadow-color:color-mix(in oklch,var(--color-amber-500)25%,transparent)}}@media (width>=48rem){.md\\:col-span-2{grid-column:span 2/span 2}.md\\:flex{display:flex}.md\\:grid-cols-2{grid-template-columns:repeat(2,minmax(0,1fr))}.md\\:grid-cols-3{grid-template-columns:repeat(3,minmax(0,1fr))}.md\\:px-6{padding-inline:calc(var(--spacing)*6)}.md\\:text-3xl{font-size:var(--text-3xl);line-height:var(--tw-leading,var(--text-3xl--line-height))}.md\\:text-4xl{font-size:var(--text-4xl);line-height:var(--tw-leading,var(--text-4xl--line-height))}.md\\:text-5xl{font-size:var(--text-5xl);line-height:var(--tw-leading,var(--text-5xl--line-height))}.md\\:text-lg{font-size:var(--text-lg);line-height:var(--tw-leading,var(--text-lg--line-height))}}@media (width>=64rem){.lg\\:grid-cols-2{grid-template-columns:repeat(2,minmax(0,1fr))}.lg\\:grid-cols-3{grid-template-columns:repeat(3,minmax(0,1fr))}.lg\\:px-8{padding-inline:calc(var(--spacing)*8)}.lg\\:text-5xl{font-size:var(--text-5xl);line-height:var(--tw-leading,var(--text-5xl--line-height))}.lg\\:text-6xl{font-size:var(--text-6xl);line-height:var(--tw-leading,var(--text-6xl--line-height))}.lg\\:text-xl{font-size:var(--text-xl);line-height:var(--tw-leading,var(--text-xl--line-height))}}@media (prefers-reduced-motion:reduce){.motion-safe\\:animate-none{animation:none}}@keyframes spin{to{transform:rotate(360deg)}}@keyframes pulse{50%{opacity:.5}}@property --tw-translate-x{syntax:\"*\";inherits:false;initial-value:0}@property --tw-translate-y{syntax:\"*\";inherits:false;initial-value:0}@property --tw-translate-z{syntax:\"*\";inherits:false;initial-value:0}@property --tw-rotate-x{syntax:\"*\";inherits:false;initial-value:rotateX(0)}@property --tw-rotate-y{syntax:\"*\";inherits:false;initial-value:rotateY(0)}@property --tw-rotate-z{syntax:\"*\";inherits:false;initial-value:rotateZ(0)}@property --tw-skew-x{syntax:\"*\";inherits:false;initial-value:skewX(0)}@property --tw-skew-y{syntax:\"*\";inherits:false;initial-value:skewY(0)}@property --tw-scale-x{syntax:\"*\";inherits:false;initial-value:1}@property --tw-scale-y{syntax:\"*\";inherits:false;initial-value:1}@property --tw-space-y-reverse{syntax:\"*\";inherits:false;initial-value:0}@property --tw-divide-y-reverse{syntax:\"*\";inherits:false;initial-value:0}@property --tw-border-style{syntax:\"*\";inherits:false;initial-value:solid}@property --tw-leading{syntax:\"*\";inherits:false}@property --tw-font-weight{syntax:\"*\";inherits:false}@property --tw-shadow{syntax:\"*\";inherits:false;initial-value:0 0 #0000}@property --tw-shadow-color{syntax:\"*\";inherits:false}@property --tw-inset-shadow{syntax:\"*\";inherits:false;initial-value:0 0 #0000}@property --tw-inset-shadow-color{syntax:\"*\";inherits:false}@property --tw-ring-color{syntax:\"*\";inherits:false}@property --tw-ring-shadow{syntax:\"*\";inherits:false;initial-value:0 0 #0000}@property --tw-inset-ring-color{syntax:\"*\";inherits:false}@property --tw-inset-ring-shadow{syntax:\"*\";inherits:false;initial-value:0 0 #0000}@property --tw-ring-inset{syntax:\"*\";inherits:false}@property --tw-ring-offset-width{syntax:\"<length>\";inherits:false;initial-value:0}@property --tw-ring-offset-color{syntax:\"*\";inherits:false;initial-value:#fff}@property --tw-ring-offset-shadow{syntax:\"*\";inherits:false;initial-value:0 0 #0000}@property --tw-blur{syntax:\"*\";inherits:false}@property --tw-brightness{syntax:\"*\";inherits:false}@property --tw-contrast{syntax:\"*\";inherits:false}@property --tw-grayscale{syntax:\"*\";inherits:false}@property --tw-hue-rotate{syntax:\"*\";inherits:false}@property --tw-invert{syntax:\"*\";inherits:false}@property --tw-opacity{syntax:\"*\";inherits:false}@property --tw-saturate{syntax:\"*\";inherits:false}@property --tw-sepia{syntax:\"*\";inherits:false}@property --tw-drop-shadow{syntax:\"*\";inherits:false}@property --tw-duration{syntax:\"*\";inherits:false}@property --tw-content{syntax:\"*\";inherits:false;initial-value:\"\"}
            </style>
        @endif

        <style>
            html { scroll-behavior: smooth; }
            .step-card {
                transition: all 0.3s ease;
            }
            .step-card:hover {
                transform: translateY(-4px);
            }
            @keyframes fadeInUp {
                from { opacity: 0; transform: translateY(20px); }
                to { opacity: 1; transform: translateY(0); }
            }
            .fade-in-up {
                animation: fadeInUp 0.6s ease-out forwards;
            }
        </style>
    </head>

    <body class="font-sans antialiased bg-gray-950 text-white">
        <!-- Navbar -->
        <nav class="fixed top-0 left-0 right-0 z-50 bg-gray-950/80 backdrop-blur-lg border-b border-white/10">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <a href="/" class="flex items-center gap-2">
                        <img src="{{ asset('images/logo.png') }}" alt="" class="h-8 w-auto">
                        <span class="text-amber-500 text-lg sm:text-xl tracking-tight" style="font-family: 'Brush Script', cursive; padding-right: 0.08em; line-height: 1.1;">Eagles</span>
                        <span class="text-white/70 font-light text-lg sm:text-xl tracking-tight whitespace-nowrap">Without Borders</span>
                    </a>

                    <div class="flex items-center gap-6">
                        @auth
                            <a href="{{ url('/dashboard') }}" class="text-sm text-white/70 hover:text-white transition-colors">
                                Dashboard
                            </a>
                        @endauth
                    </div>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <section class="relative min-h-screen flex items-center justify-center overflow-hidden pt-16">
            <!-- Background effects -->
            <div class="absolute inset-0 -z-10">
                <div class="absolute inset-0 bg-gradient-to-b from-gray-950 via-gray-900 to-black"></div>
                <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[800px] h-[800px] bg-amber-500/10 rounded-full blur-3xl"></div>
                <div class="absolute top-1/3 right-0 w-[400px] h-[400px] bg-amber-600/5 rounded-full blur-3xl"></div>
                <div class="absolute bottom-0 left-0 w-[600px] h-[600px] bg-amber-700/5 rounded-full blur-3xl"></div>
            </div>

            <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <div class="fade-in-up">
                    <!-- Badge -->
                    <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-amber-500/10 border border-amber-500/20 text-amber-400 text-xs font-semibold tracking-wider uppercase mb-8">
                        <span class="size-2 rounded-full bg-amber-400 animate-pulse"></span>
                        Membership Application
                    </div>

                    <h1 class="text-5xl sm:text-6xl lg:text-7xl font-black tracking-tight leading-tight mb-6">
                        Become an
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-400 via-amber-500 to-amber-600" style="font-family: 'Brush Script', cursive; padding-right: 0.2em; line-height: 1.1;">Eagle</span>
                    </h1>

                    <p class="text-lg sm:text-xl text-gray-400 max-w-2xl mx-auto mb-10 leading-relaxed">
                        Join Eagles Without Borders and be part of a community dedicated to service, leadership, and making a difference in the lives of others.
                    </p>

                    <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                        <a
                            href="#how-to-join"
                            class="inline-flex items-center gap-2 px-8 py-3.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-gray-950 font-bold text-base transition-all hover:shadow-xl hover:shadow-amber-500/25"
                        >
                            Start Your Journey
                            <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                            </svg>
                        </a>

                    </div>
                </div>
            </div>

            <!-- Bottom fade -->
            <div class="absolute bottom-0 left-0 right-0 h-32 bg-gradient-to-t from-gray-950 to-transparent"></div>
        </section>

        <!-- How to Join Section -->
        <section id="how-to-join" class="relative py-24 scroll-mt-24">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Section header -->
                <div class="text-center mb-16">
                    <span class="inline-block text-amber-500 font-semibold text-sm tracking-wider uppercase mb-3">The Five I's</span>
                    <h2 class="text-4xl sm:text-5xl font-black tracking-tight mb-4">
                        How to Become a
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-400 to-amber-600">Member</span>
                    </h2>
                    <p class="text-gray-400 max-w-3xl mx-auto text-base sm:text-lg leading-relaxed">
                        An aspirant or applicant shall only be inducted as a Regular Eagle Member if one has attended and completed the following basic requirements of the Philippine Eagles Institute of Leadership (PEIL).<br>
                        <span class="text-amber-500/80 font-medium">The Five I's — The process of accepting new Eagle members.</span>
                    </p>
                </div>

                <!-- Steps timeline -->
                <div class="relative max-w-4xl mx-auto">
                    <!-- Center line (desktop) -->
                    <div class="hidden lg:block absolute left-1/2 top-0 bottom-0 w-0.5 bg-gradient-to-b from-amber-500 via-amber-400 to-amber-700 -translate-x-1/2"></div>

                    @php
                        $steps = [
                            [
                                'number' => '01',
                                'title' => 'Interview',
                                'subtitle' => 'Pre-Orientation',
                                'description' => 'Prior to the Pre-orientation, the applicant must submit his Letter of Intent (LOI) before his admission to the first stage of the process. In this stage, the applicant will be subjected to a background investigation and individual interview before a Screening Committee which we call the "Jury". Here, the panel will seek to find out whether the applicant possesses the ideal qualities, including but not limited to:',
                                'qualities' => [
                                    ['label' => 'CHARACTER', 'value' => 'Honor, Loyalty, and Integrity'],
                                    ['label' => 'SERVICE', 'value' => 'Community, Club, and Fraternity'],
                                    ['label' => 'AVAILABILITY', 'value' => 'Meetings, Assemblies, and Projects'],
                                    ['label' => 'FINANCIAL STABILITY', 'value' => 'Livelihood, Employment, and Profession'],
                                    ['label' => 'PERSEVERANCE', 'value' => 'Patience, Humility, and Dedication'],
                                    ['label' => 'FELLOWSHIP', 'value' => 'Brotherhood, Sisterhood and Group Unity'],
                                ],
                                'color' => 'from-amber-400 to-amber-500',
                            ],
                            [
                                'number' => '02',
                                'title' => 'Introduction',
                                'subtitle' => 'Post-Interview, Panel of Interview',
                                'description' => 'Applicants who have successfully passed the interview shall be presented and properly introduced to all Eagles Club members during the General Membership Meeting (GMM). After the presentation of applicants, their names shall be circulated to all Eagles Clubs and Regions nationwide and shall be posted on our official web site.',
                                'extra' => 'If there are members who have knowledge or justifiable reason to believe why a particular applicant should not be accepted to The Fraternal Order of Eagles (Philippine Eagles), the said members with good standing must make their objections before the Jury, in writing, immediately or at any stage of the process, but not after the contested applicant\'s induction.',
                                'color' => 'from-amber-500 to-amber-600',
                            ],
                            [
                                'number' => '03',
                                'title' => 'Initiation',
                                'subtitle' => 'Memorize Eagleism, Universal Prayer, Eagles Pledge and Hymn',
                                'description' => 'At this core initiation stage, aspirants who have satisfactorily completed the Interview and Introduction stages will now be accorded the rare opportunity to exercise the solemn rites of Eagleism. At this stage, they shall be called Applicants.',
                                'color' => 'from-amber-500 to-amber-600',
                            ],
                            [
                                'number' => '04',
                                'title' => 'Incubation',
                                'subtitle' => 'Attend 3 GMM and Community Services',
                                'description' => 'The fourth stage of the process is a time-bound test which is designed primarily to gauge not only the determination but also the perseverance of the Applicants. After undergoing the Initiation proper, the applicants will have to endure a waiting period of three (3) months (known as the Incubation Stage) wherein they shall be required to attend the monthly General Membership Meetings and perform three (3) Community Services without fail.',
                                'extra' => 'They must attend meetings in distinct attire that will identify them as Eagle Applicants. Failure to attend in any of these meetings and community services will cause the deferment of the applicants\' induction and they will have to wait for the next batch of aspirants.',
                                'color' => 'from-amber-500 to-amber-600',
                            ],
                            [
                                'number' => '05',
                                'title' => 'Induction',
                                'subtitle' => 'PEIL Orientation and Ceremony',
                                'description' => 'Applicants who have successfully completed the first four stages shall earn the Rite of Passage, which is the last stage of the process. A ceremony shall be held in honor of recognition for their enduring fidelity to the entire process. They have proven themselves worthy to join the ranks of the Philippine Eagles and therefore now deserving to be called as "Kuya".',
                                'extra' => 'The successful applicants will now take their solemn oath of Eagleism as their final operative act to becoming a full-pledge member of an Eagle Club, and consequently, of The Fraternal Order of Eagles (Philippine Eagles).',
                                'color' => 'from-amber-600 to-amber-700',
                            ],
                        ];
                    @endphp

                    @foreach ($steps as $index => $step)
                        <div class="relative flex flex-col lg:flex-row items-start gap-6 lg:gap-8 mb-12 lg:mb-16 last:mb-0">
                            <!-- Mobile: number badge -->
                            <div class="lg:hidden flex items-center gap-4 mb-2">
                                <span class="inline-flex items-center justify-center size-10 rounded-full bg-gradient-to-br {{ $step['color'] }} text-gray-950 font-black text-sm">
                                    {{ $step['number'] }}
                                </span>
                                <div>
                                    <h3 class="text-xl font-bold">{{ $step['title'] }}</h3>
                                    <p class="text-sm text-amber-400/80">{{ $step['subtitle'] }}</p>
                                </div>
                            </div>

                            <!-- Desktop: alternating layout -->
                            <div class="hidden lg:flex w-full items-center gap-8 {{ $index % 2 === 0 ? '' : 'flex-row-reverse' }}">
                                <!-- Content side -->
                                <div class="flex-1">
                                    <div class="step-card bg-white/5 backdrop-blur-sm rounded-2xl p-6 lg:p-8 border border-white/10 hover:border-amber-500/30 transition-all hover:shadow-lg hover:shadow-amber-500/5">
                                        <div class="flex items-start gap-4">
                                            <span class="inline-flex items-center justify-center size-12 shrink-0 rounded-xl bg-gradient-to-br {{ $step['color'] }} text-gray-950 font-black text-lg">
                                                {{ $step['number'] }}
                                            </span>
                                            <div>
                                                <h3 class="text-xl lg:text-2xl font-bold mb-1">{{ $step['title'] }}</h3>
                                                <p class="text-sm text-amber-400/80 font-medium mb-3">{{ $step['subtitle'] }}</p>
                                                <p class="text-gray-400 leading-relaxed">{{ $step['description'] }}</p>

                                                @if(!empty($step['qualities']))
                                                    <div class="mt-3 flex flex-wrap gap-1.5">
                                                        @foreach($step['qualities'] as $quality)
                                                            <span class="inline-flex items-center gap-1 rounded-md bg-white/5 px-2 py-1 border border-white/5 text-[11px] leading-none">
                                                                <span class="font-bold text-amber-500 whitespace-nowrap">{{ $quality['label'] }}</span>
                                                                <span class="text-gray-500 hidden sm:inline">·</span>
                                                                <span class="text-gray-400">{{ $quality['value'] }}</span>
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                @endif

                                                @if(!empty($step['extra']))
                                                    <p class="text-gray-500 leading-relaxed mt-3 pt-3 border-t border-white/5">{{ $step['extra'] }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Timeline dot -->
                                <div class="relative flex-shrink-0 z-10">
                                    <div class="size-6 rounded-full bg-gray-950 border-4 border-amber-500 ring-1 ring-amber-500/20"></div>
                                </div>

                                <!-- Empty spacer for alternating -->
                                <div class="flex-1"></div>
                            </div>

                            <!-- Mobile: full width card -->
                            <div class="lg:hidden w-full">
                                <div class="step-card bg-white/5 backdrop-blur-sm rounded-2xl p-6 border border-white/10 hover:border-amber-500/30 transition-all">
                                    <p class="text-gray-400 leading-relaxed">{{ $step['description'] }}</p>

                                    @if(!empty($step['qualities']))
                                        <div class="mt-3 flex flex-wrap gap-1.5">
                                            @foreach($step['qualities'] as $quality)
                                                <span class="inline-flex items-center gap-1 rounded-md bg-white/5 px-2 py-1 border border-white/5 text-[11px] leading-none">
                                                    <span class="font-bold text-amber-500 whitespace-nowrap">{{ $quality['label'] }}</span>
                                                    <span class="text-gray-500">·</span>
                                                    <span class="text-gray-400">{{ $quality['value'] }}</span>
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif

                                    @if(!empty($step['extra']))
                                        <p class="text-gray-500 leading-relaxed mt-3 pt-3 border-t border-white/5">{{ $step['extra'] }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="relative py-24 overflow-hidden">
            <!-- Background -->
            <div class="absolute inset-0 -z-10">
                <div class="absolute inset-0 bg-gradient-to-b from-gray-950 via-gray-900 to-black"></div>
                <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[600px] h-[600px] bg-amber-500/5 rounded-full blur-3xl"></div>
            </div>

            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <div class="relative bg-gradient-to-br from-gray-900 to-gray-950 rounded-3xl p-8 sm:p-12 lg:p-16 border border-amber-500/20 shadow-2xl">
                    <!-- Decorative elements -->
                    <div class="absolute -top-4 -right-4 size-20 bg-amber-500/10 rounded-full blur-2xl"></div>
                    <div class="absolute -bottom-4 -left-4 size-20 bg-amber-600/10 rounded-full blur-2xl"></div>

                    <span class="inline-block text-amber-500 font-semibold text-sm tracking-wider uppercase mb-4">Join Us</span>

                    <h2 class="text-4xl sm:text-5xl lg:text-6xl font-black tracking-tight mb-6 leading-tight">
                        Are You Ready to Become an
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-400 to-amber-600" style="font-family: 'Brush Script', cursive; padding-right: 0.2em; line-height: 1.1;">Eagle</span>?
                    </h2>

                    <p class="text-lg text-gray-400 max-w-2xl mx-auto mb-10 leading-relaxed">
                        Start one of our programs today and help people in need. Take the first step toward a lifetime of service, brotherhood, and leadership.
                    </p>

                    <button
                        type="button"
                        class="inline-flex items-center gap-2 px-10 py-4 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-gray-950 font-bold text-lg transition-all hover:shadow-xl hover:shadow-amber-500/25 cursor-pointer"
                    >
                        Apply Now
                        <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </button>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="border-t border-white/10 py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="flex items-center gap-2">
                        <span class="text-amber-500" style="font-family: 'Brush Script', cursive; padding-right: 0.08em; line-height: 1.1;">Eagles</span>
                        <span class="text-white/50 font-light">Without Borders</span>
                    </div>
                    <div class="flex items-center gap-4">
                        <a href="https://www.facebook.com/groups/863084874785698" target="_blank" rel="noopener noreferrer" class="text-gray-500 hover:text-blue-400 transition-colors" title="Facebook Group">
                            <svg class="size-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z"/>
                            </svg>
                        </a>
                        <img src="{{ asset('images/logo.png') }}" alt="" class="h-6 w-auto opacity-50">
                        <span class="text-gray-700">|</span>
                        <a href="{{ route('login') }}" class="text-xs text-gray-600 hover:text-gray-400 transition-colors">
                            Admin Login
                        </a>
                        <span class="text-gray-700">|</span>
                        <p class="text-sm text-gray-500">
                            &copy; {{ date('Y') }} Eagles Without Borders. All rights reserved.
                        </p>
                    </div>
                </div>
            </div>
        </footer>
    </body>
</html>
