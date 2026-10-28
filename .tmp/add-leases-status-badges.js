const root=figma.currentPage.findAll(n=>n.id==='284:6636')[0];
const table=root?.children.find(n=>n.name==='Main')?.children.find(n=>n.name==='Content')?.children.find(n=>n.y===291); if(!table)return {error:'table missing'};
await Promise.all([figma.loadFontAsync({family:'Figtree',style:'Regular'}),figma.loadFontAsync({family:'Figtree',style:'Bold'})]);
const green={r:0,g:.5608,b:.4},pale={r:.902,g:.9804,b:.949}; const solid=c=>[{type:'SOLID',color:c}]; const created=[];
for(const row of table.children.filter(n=>n.type==='FRAME'&&n.y>=65)){
  const status=row.children.find(n=>n.type==='TEXT'&&n.characters==='Active'); if(status){status.visible=true;status.opacity=0;}
  if(row.children.some(n=>n.name==='Active status badge'))continue;
  const b=figma.createFrame();b.name='Active status badge';b.resize(72,32);b.fills=solid(pale);b.cornerRadius=18;row.appendChild(b);b.layoutPositioning='ABSOLUTE';b.x=1070;b.y=25;
  const t=figma.createText();t.name='Status badge label';t.fontName={family:'Figtree',style:'Bold'};t.characters='Active';t.fontSize=14;t.fills=solid(green);t.textAlignHorizontal='CENTER';t.resize(72,18);row.appendChild(t);t.layoutPositioning='ABSOLUTE';t.x=1070;t.y=32;created.push(b.id,t.id);
}
return {updated:root.id,createdNodeIds:created,prototypeConnectionsChanged:false};
