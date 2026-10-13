const nodes=figma.currentPage.findAll(n=>n.type==='FRAME'&&n.name.startsWith('NEW FLOW / Admin HD /'));
const ids=nodes.map(n=>n.id);
for(const n of nodes)n.remove();
return {deleted:ids.length,ids};
