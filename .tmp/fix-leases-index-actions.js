const root=figma.currentPage.findAll(n=>n.id==='284:6636')[0];
const content=root?.children.find(n=>n.name==='Main')?.children.find(n=>n.name==='Content');
const table=content?.children.find(n=>n.y===291);
if(!table)return {error:'Lease table not found'};
await Promise.all([figma.loadFontAsync({family:'Figtree',style:'Regular'}),figma.loadFontAsync({family:'Figtree',style:'Bold'})]);
const C={muted:{r:.3922,g:.4549,b:.5451},green:{r:0,g:.5608,b:.4},greenPale:{r:.902,g:.9804,b:.949},pale:{r:.9569,g:.9686,b:.9843},white:{r:1,g:1,b:1}};
const solid=c=>[{type:'SOLID',color:c}];
const svg={
 eye:'<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z" stroke="#64748B" stroke-width="1.8" stroke-linejoin="round"/><circle cx="12" cy="12" r="2.5" stroke="#64748B" stroke-width="1.8"/></svg>',
 edit:'<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4Z" stroke="#64748B" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>',
 trash:'<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M3 6h18M8 6V3h8v3M19 6l-1 15H6L5 6M10 10v7M14 10v7" stroke="#EF4444" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>'
};
const created=[];
const addSvg=(p,name,markup,x,y)=>{const existing=p.children.find(n=>n.name===name);if(existing){existing.layoutPositioning='ABSOLUTE';existing.x=x;existing.y=y;return;}const n=figma.createNodeFromSvg(markup);n.name=name;n.resize(22,22);p.appendChild(n);n.layoutPositioning='ABSOLUTE';n.x=x;n.y=y;created.push(n.id)};
const rows=[['Juanito Mico','JM','Lease #3'],['Jm Carig','JC','Lease #2'],['JOHN MICHAEL CARIG','JC','Lease #1']];
table.children.filter(n=>n.type==='FRAME'&&n.y>=65).forEach((row,i)=>{
  const data=rows[i]; if(!data)return;
  const ell=row.children.find(n=>n.type==='TEXT'&&n.x>1200&&n.width>180); if(ell)ell.visible=false;
  addSvg(row,'Action / view',svg.eye,1270,30); addSvg(row,'Action / edit',svg.edit,1330,30); addSvg(row,'Action / delete',svg.trash,1390,30);
  const name=row.children.find(n=>n.type==='TEXT'&&n.characters===data[0]); if(name){name.layoutPositioning='ABSOLUTE';name.x=92;name.y=20;name.resize(300,19);}
  const avExisting=row.children.find(n=>n.name==='Tenant avatar'); if(avExisting){avExisting.layoutPositioning='ABSOLUTE';avExisting.x=26;avExisting.y=16;}
  if(!avExisting){const av=figma.createEllipse();av.name='Tenant avatar';av.resize(50,50);av.fills=solid(C.pale);row.appendChild(av);av.layoutPositioning='ABSOLUTE';av.x=26;av.y=16;created.push(av.id);}
  const initialsExisting=row.children.find(n=>n.name==='Tenant initials'); if(initialsExisting){initialsExisting.layoutPositioning='ABSOLUTE';initialsExisting.x=26;initialsExisting.y=16;}
  if(!initialsExisting){const it=figma.createText();it.name='Tenant initials';it.fontName={family:'Figtree',style:'Bold'};it.characters=data[1];it.fontSize=14;it.fills=solid(C.muted);it.textAlignHorizontal='CENTER';it.textAlignVertical='CENTER';it.resize(50,50);row.appendChild(it);it.layoutPositioning='ABSOLUTE';it.x=26;it.y=16;created.push(it.id);}
  const subExisting=row.children.find(n=>n.name==='Lease reference'); if(subExisting){subExisting.layoutPositioning='ABSOLUTE';subExisting.x=92;subExisting.y=46;}
  if(!subExisting){const sub=figma.createText();sub.name='Lease reference';sub.fontName={family:'Figtree',style:'Regular'};sub.characters=data[2];sub.fontSize=14;sub.fills=solid(C.muted);sub.resize(300,18);row.appendChild(sub);sub.layoutPositioning='ABSOLUTE';sub.x=92;sub.y=46;created.push(sub.id);}
  const status=row.children.find(n=>n.type==='TEXT'&&n.characters==='Active'); if(status){status.visible=false;}
  const badgeExisting=row.children.find(n=>n.name==='Active status badge'); if(badgeExisting){badgeExisting.layoutPositioning='ABSOLUTE';badgeExisting.x=1070;badgeExisting.y=25;}
  if(!badgeExisting){const b=figma.createFrame();b.name='Active status badge';b.resize(72,32);b.fills=solid(C.greenPale);b.cornerRadius=18;row.appendChild(b);b.layoutPositioning='ABSOLUTE';b.x=1070;b.y=25;const t=figma.createText();t.name='Status';t.fontName={family:'Figtree',style:'Bold'};t.characters='Active';t.fontSize=14;t.fills=solid(C.green);t.resize(72,18);t.textAlignHorizontal='CENTER';row.appendChild(t);t.layoutPositioning='ABSOLUTE';t.x=1070;t.y=32;created.push(b.id,t.id);}
});
return {updated:root.id,createdNodeIds:created,prototypeConnectionsChanged:false};
