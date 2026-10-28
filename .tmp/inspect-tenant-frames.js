const ids = ['284:6189','284:6327','284:6430','284:6533'];
const out = [];
for (const id of ids) {
  const root = figma.currentPage.findAll(n => n.id === id)[0];
  const main = root?.children.find(n => n.name === 'Main');
  const content = main?.children.find(n => n.name === 'Content');
  out.push({ id, rootFound:!!root, contentId:content?.id, childCount:content?.children.length, children:content?.children.map(n => ({id:n.id,name:n.name,type:n.type,x:n.x,y:n.y,w:n.width,h:n.height,childCount:n.children?.length??null})) });
}
return out;
