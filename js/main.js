/* ── NAV ── */
function tNav(){
  const m=document.getElementById('mmenu');
  const b=document.querySelector('.hbg');
  const o=m.classList.toggle('open');
  b.setAttribute('aria-expanded',o);
}
function cNav(){
  document.getElementById('mmenu').classList.remove('open');
  document.querySelector('.hbg').setAttribute('aria-expanded','false');
}
document.addEventListener('click',e=>{
  if(!document.getElementById('nav').contains(e.target)) cNav();
});

/* ── REVEAL ── */
const ro=new IntersectionObserver(entries=>{
  entries.forEach(e=>{if(e.isIntersecting){e.target.classList.add('on');ro.unobserve(e.target)}});
},{threshold:.11});
document.querySelectorAll('.rv').forEach(el=>ro.observe(el));

/* ── TODAY SCHEDULE ── */
function todaySched(){
  const d=new Date().getDay();
  if(d===0||d===6)return'Hoy: 7:00 AM – 5:00 PM';
  const n=['','Lunes','Martes','Miércoles','Jueves','Viernes'];
  return n[d]+': 6:00 AM – 10:00 PM';
}
const th=document.getElementById('today-h');
if(th)th.textContent=todaySched();

/* ── FAQ ── */
function tFaq(btn){
  const item=btn.closest('.fi');
  const wasOpen=item.classList.contains('open');
  document.querySelectorAll('.fi.open').forEach(i=>{
    i.classList.remove('open');
    i.querySelector('.fq').setAttribute('aria-expanded','false');
  });
  if(!wasOpen){
    item.classList.add('open');
    btn.setAttribute('aria-expanded','true');
  }
}

/* ── NAV SCROLL ── */
window.addEventListener('scroll',()=>{
  const ni=document.querySelector('.ni');
  ni.style.background=window.scrollY>50?'rgba(11,11,15,.95)':'rgba(11,11,15,.7)';
},{passive:true});
