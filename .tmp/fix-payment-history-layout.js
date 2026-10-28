const rows=[['282:1685','282:1687',['282:1688','282:1689']],['282:1692','282:1694',['282:1695','282:1696']]];
for(const [rowId,copyId,textIds] of rows){const row=await figma.getNodeByIdAsync(rowId),copy=await figma.getNodeByIdAsync(copyId);row.resize(996,52);copy.resize(700,36);for(const id of textIds){const t=await figma.getNodeByIdAsync(id);t.textAutoResize='NONE';t.resize(700,16);}}
return {fixed:rows.map(x=>x[0])};
