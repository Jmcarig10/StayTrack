const root=figma.currentPage.findAll(n=>n.id==='284:6636')[0];
const content=root?.children.find(n=>n.name==='Main')?.children.find(n=>n.name==='Content');
const table=content?.children.find(n=>n.y===291);
if(!table)return {error:'Lease table not found'};
const removed=[];
for(const row of table.children.filter(n=>n.type==='FRAME'&&n.y>=65)){
  for(const n of [...row.children]){
    if(['Tenant avatar','Tenant initials','Lease reference','Active status badge'].includes(n.name) || (n.name==='Status'&&n.type==='TEXT'&&n.characters==='Active')){removed.push(n.id);n.remove();}
  }
  const name=row.children.find(n=>n.type==='TEXT'&&['Juanito Mico','Jm Carig','JOHN MICHAEL CARIG'].includes(n.characters)); if(name){name.layoutPositioning='AUTO';name.x=0;name.y=0;}
  const status=row.children.find(n=>n.type==='TEXT'&&n.characters==='Active'); if(status){status.visible=true;status.layoutPositioning='AUTO';}
  const ell=row.children.find(n=>n.type==='TEXT'&&n.x>1200&&n.width>180); if(ell)ell.visible=false;
  row.children.filter(n=>/^Action \/ /.test(n.name||'')).forEach(n=>{n.layoutPositioning='ABSOLUTE';});
}
return {updated:root.id,removedNodeIds:removed,prototypeConnectionsChanged:false};
