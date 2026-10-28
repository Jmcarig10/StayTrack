const root = await figma.getNodeByIdAsync('277:1304');
const texts = root.findAll((n) => n.type === 'TEXT').map((n) => ({id:n.id, chars:n.characters}));
const hits = texts.filter((t) => ['Cancel','Save changes','Edit'].includes(t.chars));
return { textCount: texts.length, hits };
