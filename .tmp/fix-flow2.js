const get = async (id) => await figma.getNodeByIdAsync(id);
for (const f of [
  {family:'Inter',style:'Regular'}, {family:'Inter',style:'Medium'}, {family:'Inter',style:'Semi Bold'}, {family:'Inter',style:'Bold'},
  {family:'Figtree',style:'Regular'}, {family:'Figtree',style:'Medium'}, {family:'Figtree',style:'Semi Bold'}, {family:'Figtree',style:'Bold'}
]) { try { await figma.loadFontAsync(f); } catch (_) {} }
const textNodes = (root) => root.findAll ? root.findAll(n => n.type === 'TEXT') : [];
const setText = (root, from, to) => { for (const n of textNodes(root)) if (n.characters === from || n.name === from) { n.characters = to; n.name = to; } };
const tint = (node, color) => { if (!node) return; if (node.strokes !== undefined && node.strokes && node.strokes.length) node.strokes = [{ type: 'SOLID', color }]; if (node.fills !== undefined && node.fills && node.fills.length && node.type === 'VECTOR') node.fills = [{ type: 'SOLID', color }]; if (node.children) for (const c of node.children) tint(c, color); };

// Registration: restore full-width vertical fields and editable icon vectors.
const reg = await get('278:3944');
const form = await get('278:3958');
const fields = await get('278:3963');
if (reg && form && fields) {
  reg.name = 'NEW FLOW / Authentication / Register';
  form.resize(420, 520);
  fields.layoutMode = 'NONE';
  fields.resize(420, 360);
  const groups = [
    ['278:3964','278:3966',0], ['278:3968','278:3970',79],
    ['278:3972','278:3974',158], ['278:3977','278:3979',237]
  ];
  for (const [gid, cid, y] of groups) {
    const g = await get(gid), c = await get(cid);
    if (!g || !c) continue;
    g.layoutMode = 'NONE'; g.resize(420, 67); g.x = 0; g.y = y;
    c.resize(420, 44); c.x = 0; c.y = 23;
  }
  const button = await get('278:3982');
  const buttonText = await get('278:3983');
  if (button) { button.resize(420,44); button.x=0; button.y=316; }
  if (buttonText) { buttonText.characters='Create account'; buttonText.x=150; buttonText.y=14; }
  const eyeSource = await get('272:396');
  for (const id of ['278:3976','278:3981']) {
    const old = await get(id); if (!old || !eyeSource) continue;
    const parent = old.parent, x = 390, y = 13;
    const icon = eyeSource.clone(); icon.name='Icon / Eye'; parent.appendChild(icon); icon.x=x; icon.y=y; icon.resize(18,18); tint(icon,{r:0.392,g:0.455,b:0.545}); old.remove();
  }
  const arrowSource = await get('272:334');
  const oldArrow = await get('278:3984');
  if (oldArrow && arrowSource) { const p=oldArrow.parent, icon=arrowSource.clone(); icon.name='Icon / Arrow'; p.appendChild(icon); icon.x=356; icon.y=14; icon.resize(16,16); tint(icon,{r:1,g:1,b:1}); oldArrow.remove(); }
  fields.name='Registration fields / Full width';
}

// Maintenance: swap the mixed Admin shell for the canonical Tenant shell.
const tenantRoot = await get('278:3503');
const main = await get('278:3548');
if (tenantRoot && main) {
  const oldSidebar = await get('278:3504'); if (oldSidebar) oldSidebar.remove();
  const tenantSidebarSource = await get('283:1900');
  if (tenantSidebarSource) { const s=tenantSidebarSource.clone(); s.name='Sidebar / Tenant'; tenantRoot.appendChild(s); s.x=0; s.y=0; }
  const oldTopbar = await get('278:3549'); if (oldTopbar) oldTopbar.remove();
  const tenantTopSource = await get('283:1968');
  if (tenantTopSource) { const t=tenantTopSource.clone(); t.name='Topbar / Tenant'; main.appendChild(t); t.x=0; t.y=0; }
  const oldContent = await get('278:3558'); if (oldContent) oldContent.remove();
  const contentSource = await get('278:2378');
  const sourceHeader = await get('278:2396');
  const sourceRowText = await get('278:2401');
  const sourceMetrics = await get('277:1131');
  if (contentSource) {
    const content = contentSource.clone(); content.name='Content / Maintenance requests'; main.appendChild(content); content.x=0; content.y=80;
    setText(content,'Review tenant requests and track repair status.','Track repair and upkeep requests across the building.');
    setText(content,'Search records...','Search title, tenant, or unit…');
    const heading = content.findAll(n=>n.type==='FRAME' && n.children && n.children.some(c=>c.type==='TEXT' && c.characters==='Maintenance requests'))[0];
    if (heading) {
      const plusSource = null;
      const button = figma.createFrame(); button.name='Button / New request'; button.resize(146,44); button.cornerRadius=8; button.fills=[{type:'SOLID',color:{r:0.059,g:0.09,b:0.165}}]; heading.appendChild(button); button.x=900; button.y=0;
      const bt = sourceRowText ? sourceRowText.clone() : null; if(bt){bt.characters='New request';bt.name='New request';bt.x=38;bt.y=14;bt.fills=[{type:'SOLID',color:{r:1,g:1,b:1}}];button.appendChild(bt);}
      if(plusSource){const pi=plusSource.clone();pi.name='Icon / Plus';pi.x=14;pi.y=14;pi.resize(16,16);tint(pi,{r:1,g:1,b:1});button.appendChild(pi);}
      const createTarget = await get('278:3601'); if(createTarget) await button.setReactionsAsync([{trigger:{type:'ON_CLICK'},actions:[{type:'NODE',destinationId:createTarget.id,navigation:'NAVIGATE',transition:{type:'DISSOLVE',duration:0.2,easing:{type:'EASE_IN_AND_OUT'}}}]}]);
    }
    const toolbar = content.findAll(n=>n.name==='Toolbar')[0];
    if(toolbar){ toolbar.x=32; toolbar.y=289; toolbar.resize(1046,44); const filter=toolbar.findAll(n=>n.type==='FRAME' && n.width===182 && n.height===36)[0]; if(filter){filter.x=646; filter.y=4; filter.resize(400,36); const chips=filter.children.filter(n=>n.type==='FRAME'); const labels=['All','Pending','In progress','Completed','Cancelled']; const widths=[43,72,86,88,79]; let x=0; for(let i=0;i<chips.length&&i<3;i++){chips[i].x=x;chips[i].resize(widths[i],36); const tx=chips[i].findAll(n=>n.type==='TEXT')[0]; if(tx){tx.characters=labels[i];tx.name=labels[i];} x+=widths[i]+8;} const template=chips[1]; for(let i=3;i<labels.length;i++){const c=template.clone();filter.appendChild(c);c.x=x;c.resize(widths[i],36);const tx=c.findAll(n=>n.type==='TEXT')[0];if(tx){tx.characters=labels[i];tx.name=labels[i];}x+=widths[i]+8;}}}
    const table = content.findAll(n=>n.name==='Table')[0];
    if(table){ table.x=32; table.y=357; table.resize(1046,300); const header=table.children.find(n=>n.type==='FRAME'&&n.y<10); if(header&&sourceHeader){for(const c of [...header.children]) if(c.type==='TEXT') c.remove(); const labels=['REQUEST','TENANT','UNIT','CATEGORY','PRIORITY','STATUS','SUBMITTED','ACTION']; const xs=[20,196,340,470,610,750,880,960]; labels.forEach((label,i)=>{const tx=sourceHeader.clone();tx.characters=label;tx.name=label;tx.x=xs[i];tx.y=16;tx.resize(i===7?70:120,15);header.appendChild(tx);});}
      const rows=table.children.filter(n=>n.type==='FRAME'&&n.y>40); const first=rows[0]; let empty=null; if(first){empty=first.clone(); for(const c of [...empty.children]) c.remove(); for(const r of rows) r.remove(); empty.name='Empty state row'; empty.x=1; empty.y=47; empty.resize(1044,62); if(sourceRowText){const tx=sourceRowText.clone();tx.characters='No maintenance requests found.';tx.name='No maintenance requests found.';tx.x=385;tx.y=23;tx.fills=[{type:'SOLID',color:{r:0.392,g:0.455,b:0.545}}];empty.appendChild(tx);} table.appendChild(empty);}
    }
    if(sourceMetrics){const metrics=sourceMetrics.clone();metrics.name='Metrics / Maintenance';content.appendChild(metrics);metrics.x=32;metrics.y=129;metrics.resize(1046,120);const cards=metrics.children.filter(n=>n.type==='FRAME'); if(cards[3])cards[3].remove(); const specs=[['Total requests','0'],['Open','0'],['Completed','0']]; cards.slice(0,3).forEach((card,i)=>{const txs=card.findAll(n=>n.type==='TEXT');if(txs[0]){txs[0].characters=specs[i][0];txs[0].name=specs[i][0];}if(txs[1]){txs[1].characters=specs[i][1];txs[1].name=specs[i][1];}for(const tx of txs.slice(2))tx.visible=false;card.x=i*352;card.resize(320,120);});}
    // Keep tenant nav links functional on the corrected shell.
    const navTargets={'My dashboard':'283:1899','My apartment':'282:1262','My lease':'282:1371','My bills':'282:1486','Payment history':'282:1596','Maintenance':'278:3503','Profile':'278:3761'};
    const sidebar=tenantRoot.children.find(n=>n.name==='Sidebar / Tenant'); if(sidebar){for(const [label,targetId] of Object.entries(navTargets)){const tx=sidebar.findAll(n=>n.type==='TEXT'&&n.characters===label)[0];const target=await get(targetId);if(tx&&target){const item=tx.parent;await item.setReactionsAsync([{trigger:{type:'ON_CLICK'},actions:[{type:'NODE',destinationId:target.id,navigation:'NAVIGATE',transition:{type:'DISSOLVE',duration:0.2,easing:{type:'EASE_IN_AND_OUT'}}}]}]);}}}
  }
  tenantRoot.name='NEW FLOW / Tenant / Maintenance / Index';
}

// Register link to login.
const loginTarget=await get('278:3890'); const registerButton=await get('278:3982'); if(loginTarget&&registerButton) await registerButton.setReactionsAsync([{trigger:{type:'ON_CLICK'},actions:[{type:'NODE',destinationId:loginTarget.id,navigation:'NAVIGATE',transition:{type:'DISSOLVE',duration:0.2,easing:{type:'EASE_IN_OUT'}}}]}]).catch(()=>{});
return {ok:true,registrationFixed:!!form,maintenanceFixed:!!tenantRoot};
