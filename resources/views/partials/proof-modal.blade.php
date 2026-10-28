@php $proofExt = strtolower(pathinfo($payment->proof_path ?? '', PATHINFO_EXTENSION)); @endphp
<div id="proof-modal" class="modal-backdrop" hidden>
    <section class="modal modal-wide" role="dialog" aria-modal="true" aria-labelledby="proof-modal-title">
        <button class="icon-button modal-close" type="button" data-proof-close aria-label="Close preview"><svg class="icon"><use href="#icon-x"/></svg></button>
        <span class="eyebrow">Payment proof</span>
        <h2 id="proof-modal-title">Proof of payment</h2>
        <div class="proof-preview">
            @if($proofExt === 'pdf')
                <embed data-proof-pdf src="{{ $proofUrl }}" type="application/pdf">
            @else
                <img data-proof-img src="{{ $proofUrl }}" alt="Proof of payment">
            @endif
        </div>
        <p class="muted"><a class="button button-small button-secondary" href="{{ $proofUrl }}" target="_blank" rel="noopener">Open in new tab</a></p>
    </section>
</div>
