const ids = ['284:6751','284:6867'];
await Promise.all([
  figma.loadFontAsync({ family: 'Figtree', style: 'Regular' }),
  figma.loadFontAsync({ family: 'Figtree', style: 'Bold' })
]);
const created=[];
const calendarSvg='<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="3" y="5" width="18" height="16" rx="2" stroke="#64748B" stroke-width="1.8"/><path d="M8 3v4M16 3v4M3 10h18" stroke="#64748B" stroke-width="1.8" stroke-linecap="round"/></svg>';
const setText=(n,w)=>{if(!n||n.type!=='TEXT')return;n.textAutoResize='NONE';n.resize(Math.max(40,w),19);n.x=17;n.y=18.5;};
const fixField=(panel,labelName,x,width,offsetY=0,calendar=false)=>{
  const label=panel.findAll(n=>n.type==='TEXT'&&n.name===labelName)[0];
  if(!label)return false;
  const field=label.parent; const group=field?.parent; const row=group?.parent;
  if(!field||!group||!row)return false;
  row.resize(1438,row.height); group.x=x; group.y=offsetY; group.resize(width,field.height); field.resize(width,field.height);
  label.textAutoResize='NONE'; label.resize(width,19); label.x=0; label.y=0;
  const input=field.children.find(n=>n.type==='FRAME');
  if(!input)return false;
  input.x=0; input.y=36; input.resize(width,56);
  const value=input.children.find(n=>n.type==='TEXT'); if(value)setText(value,width-54);
  const chevron=input.children.find(n=>/chevron/.test(n.name||'')); if(chevron){chevron.x=width-36;chevron.y=18;}
  if(calendar && !input.children.some(n=>n.name==='Icon / calendar')){const cal=figma.createNodeFromSvg(calendarSvg);cal.name='Icon / calendar';cal.resize(20,20);cal.x=width-38;cal.y=18;input.appendChild(cal);created.push(cal.id);}
  return true;
};
const fixed=[];
for(const id of ids){
  const root=figma.currentPage.findAll(n=>n.id===id)[0];
  const content=root?.children.find(n=>n.name==='Main')?.children.find(n=>n.name==='Content');
  const panel=content?.children.find(n=>n.y===171);
  if(!panel)continue;
  const isCreate=id==='284:6751';
  if(isCreate){
    ['Tenant *','Start date *','Monthly rent *'].forEach((n)=>{if(fixField(panel,n,0,680,n==='Tenant *'?0:0,n!=='Tenant *'&&n!=='Monthly rent *'))fixed.push(n);});
    ['Apartment *','End date','Security deposit'].forEach(n=>{if(fixField(panel,n,720,680,0,n==='End date'))fixed.push(n);});
    panel.findAll(n=>n.type==='TEXT'&&n.characters==='PHP').forEach(n=>{n.characters='₱';});
  }else{
    ['Name / tenant','Apartment'].forEach(n=>{if(fixField(panel,n,0,680,0,false))fixed.push(n);});
    ['Status','Reference'].forEach(n=>{if(fixField(panel,n,720,680,0,false))fixed.push(n);});
  }
  const actions=panel.findAll(n=>n.type==='FRAME'&&n.children?.some(c=>c.type==='TEXT'&&c.characters==='Cancel'))[0];
  if(actions){actions.x=1520-actions.width-41;}
}
return {updated:ids, fixedFields:fixed, createdNodeIds:created, prototypeConnectionsChanged:false};
