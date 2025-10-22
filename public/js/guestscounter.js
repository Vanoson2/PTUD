// Minimal JS to update guests without reloading
(function(){
  function ready(fn){
    if (document.readyState !== 'loading') fn();
    else document.addEventListener('DOMContentLoaded', fn);
  }
  ready(function(){
    var ctr = document.getElementById('guestCounter');
    if (!ctr) return;
    var input = document.getElementById('guestsInput');
    var minus = ctr.querySelector('.btn-guest.minus');
    var plus = ctr.querySelector('.btn-guest.plus');
    if (!input || !minus || !plus) return;

    var MIN = Number(input.getAttribute('min')) || 1;
    var MAX = Number(input.getAttribute('max')) || 10;

    function toInt(v){ var n = parseInt(v,10); return isNaN(n)?MIN:n; }
    function clamp(n){ return Math.max(MIN, Math.min(MAX, n)); }
    function sync(){
      var v = toInt(input.value);
      minus.disabled = v <= MIN;
      plus.disabled = v >= MAX;
    }

    minus.addEventListener('click', function(e){
      e.preventDefault();
      input.value = String(clamp(toInt(input.value) - 1));
      sync();
    });
    plus.addEventListener('click', function(e){
      e.preventDefault();
      input.value = String(clamp(toInt(input.value) + 1));
      sync();
    });
    sync();
  });
})();
