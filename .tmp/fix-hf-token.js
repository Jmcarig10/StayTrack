const ids = ['282:1686','282:1693'];
for (const id of ids) { const n = await figma.getNodeByIdAsync(id); if (n) n.fills = [{ type:'SOLID', color:{r:0.059,g:0.545,b:0.345} }]; }
return {updated:ids};
