const old = await figma.getNodeByIdAsync('278:3420');
const fresh = await figma.getNodeByIdAsync('283:1899');
old.name = 'NEW FLOW / Tenant / Dashboard / Draft';
fresh.name = 'NEW FLOW / Tenant / Dashboard';
return {renamed:[old.id,fresh.id]};
