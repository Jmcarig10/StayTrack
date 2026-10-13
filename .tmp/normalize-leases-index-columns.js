const root=figma.currentPage.findAll(n=>n.id==='284:6636')[0];
const content=root?.children.find(n=>n.name==='Main')?.children.find(n=>n.name==='Content');
const table=content?.children.find(n=>n.y===291); if(!table)return {error:'Lease table missing'};
const rows=['Juanito Mico','Jm Carig','JOHN MICHAEL CARIG'];
table.children.filter(n=>n.type==='FRAME'&&n.y>=65).forEach((row,i)=>{
  const name=row.children.find(n=>n.type==='TEXT'&&n.characters===rows[i]); if(name){name.layoutPositioning='AUTO';name.resize(209.14285,19);}
  const status=row.children.find(n=>n.type==='TEXT'&&n.characters==='Active'); if(status){status.visible=true;status.layoutPositioning='AUTO';}
  const ell=row.children.find(n=>n.type==='TEXT'&&n.x>1200&&n.width>180); if(ell){ell.visible=true;ell.opacity=0;}
  row.children.filter(n=>/^Action \/ /.test(n.name||'')).forEach((n,j)=>{n.layoutPositioning='ABSOLUTE';n.x=1270+j*60;n.y=30;});
});
return {updated:root.id,prototypeConnectionsChanged:false};
