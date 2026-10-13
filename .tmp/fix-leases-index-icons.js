const root = figma.currentPage.findAll(n => n.id === '284:6636')[0];
if (!root) return { error: 'Leases frame not found' };
const main = root.children.find(n => n.name === 'Main');
const content = main?.children.find(n => n.name === 'Content');
const panel = content?.children.find(n => n.name === 'Leases table');
const addButton = content?.children.find(n => n.name === 'Action / Create lease');
if (!content || !panel || !addButton) return { error: 'Expected Leases nodes missing' };
const svg = {
  plus: '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 5v14M5 12h14" stroke="#FFFFFF" stroke-width="1.8" stroke-linecap="round"/></svg>',
  eye: '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z" stroke="#64748B" stroke-width="1.8" stroke-linejoin="round"/><circle cx="12" cy="12" r="2.5" stroke="#64748B" stroke-width="1.8"/></svg>',
  edit: '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4Z" stroke="#64748B" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>',
  trash: '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M3 6h18M8 6V3h8v3M19 6l-1 15H6L5 6M10 10v7M14 10v7" stroke="#EF4444" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>'
};
const created=[];
const add=(parent,name,markup,x,y,size)=>{if(parent.children.some(n=>n.name===name))return;const n=figma.createNodeFromSvg(markup);n.name=name;n.resize(size,size);n.x=x;n.y=y;parent.appendChild(n);created.push(n.id);};
add(addButton,'Icon / plus',svg.plus,18,16,24);
['Juanito Mico','Jm Carig','JOHN MICHAEL CARIG'].forEach((name,i)=>{const y=156+i*91+32;add(panel,'Action / view '+name,svg.eye,1200,y,22);add(panel,'Action / edit '+name,svg.edit,1260,y,22);add(panel,'Action / delete '+name,svg.trash,1320,y,22);});
return { updated:root.id, createdNodeIds:created, prototypeConnectionsChanged:false };
