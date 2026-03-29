// Centralized Weather Effects + Particles + Lightning
const container = document.getElementById('weather-particles');
const lightningEl = document.getElementById('lightning');

function rain(){for(let i=0;i<120;i++){let d=document.createElement('div');d.className='rain-drop';d.style.left=Math.random()*100+'vw';d.style.animationDuration=(0.5+Math.random())+'s';container.appendChild(d);}}
function snow(){for(let i=0;i<80;i++){let s=document.createElement('div');s.className='snow-flake';s.style.left=Math.random()*100+'vw';s.style.animationDuration=(3+Math.random()*5)+'s';container.appendChild(s);}}

const bodyClass=document.body.className;
if(bodyClass.includes('rainy-bg')) rain();
if(bodyClass.includes('snowy-bg')) snow();
if(bodyClass.includes('stormy-bg')){
    setInterval(()=>{ if(Math.random()>0.7){ lightningEl.style.opacity=1; setTimeout(()=>lightningEl.style.opacity=0,120); } },2500);
}
