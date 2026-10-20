document.addEventListener('DOMContentLoaded', () => {
 const sidebar=document.getElementById('sidebar');
 document.getElementById('menuToggle')?.addEventListener('click',()=>sidebar?.classList.toggle('open'));
 document.addEventListener('click',e=>{if(sidebar?.classList.contains('open') && !sidebar.contains(e.target) && !e.target.closest('#menuToggle'))sidebar.classList.remove('open');});
 document.querySelectorAll('form[data-confirm]').forEach(f=>f.addEventListener('submit',e=>{if(!confirm(f.dataset.confirm||'هل أنت متأكد؟'))e.preventDefault()}));
 const workType=document.querySelector('[data-work-type]');const price=document.querySelector('[data-price]');
 if(workType && price){workType.addEventListener('change',()=>{const item=workType.selectedOptions[0]; if(item?.dataset.price && !price.dataset.touched)price.value=item.dataset.price;}); price.addEventListener('input',()=>{price.dataset.touched='1';});}
 const doctorSelect=document.querySelector('[data-doctor]');const patientSelect=document.querySelector('[data-patient]');
 if(doctorSelect&&patientSelect){const update=()=>{for(const option of patientSelect.options){if(!option.value)continue;const owner=option.dataset.doctor;option.hidden=Boolean(owner&&doctorSelect.value&&owner!==doctorSelect.value);}if(patientSelect.selectedOptions[0]?.hidden)patientSelect.value='';};doctorSelect.addEventListener('change',update);update();}
});
