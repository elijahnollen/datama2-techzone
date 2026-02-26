/* global db, ObjectId */

const fs = require('fs');
const path = require('path');

const schemaDirArg = process.argv[2];
const dbNameArg = process.argv[3];

const schemaDir = schemaDirArg && schemaDirArg.trim() !== ''
  ? schemaDirArg
  : path.join(process.cwd(), 'backend', 'mongodb_collection_schemas');
const dbName = dbNameArg && dbNameArg.trim() !== '' ? dbNameArg : 'techzone';

const database = db.getSiblingDB(dbName);

function convertExtendedJson(value) {
  if (Array.isArray(value)) {
    return value.map(convertExtendedJson);
  }

  if (value && typeof value === 'object') {
    const keys = Object.keys(value);
    if (keys.length === 1 && keys[0] === '$oid' && typeof value.$oid === 'string') {
      try {
        return new ObjectId(value.$oid);
      } catch (_error) {
        return value.$oid;
      }
    }
    if (keys.length === 1 && keys[0] === '$date') {
      return new Date(value.$date);
    }

    const output = {};
    for (const key of keys) {
      output[key] = convertExtendedJson(value[key]);
    }
    return output;
  }

  return value;
}

function parseSchemaContent(content) {
  try {
    return JSON.parse(content);
  } catch (_jsonError) {
    // Fallback for schema-style JSON files containing comments.
    // Files are local project assets and treated as trusted input.
    return eval(`(${content})`);
  }
}

function seedCollection(fileName) {
  const collection = fileName.replace(/\.json$/i, '');
  const fullPath = path.join(schemaDir, fileName);
  const raw = fs.readFileSync(fullPath, 'utf8');
  const parsed = parseSchemaContent(raw);

  const docs = Array.isArray(parsed) ? parsed : [parsed];
  const normalized = docs
    .filter((doc) => doc && typeof doc === 'object')
    .map((doc) => convertExtendedJson(doc));

  database[collection].deleteMany({});
  if (normalized.length > 0) {
    database[collection].insertMany(normalized, { ordered: false });
  }

  print(`[mongo-seed] ${collection}: ${normalized.length} document(s) seeded`);
}

if (!fs.existsSync(schemaDir)) {
  throw new Error(`Schema directory not found: ${schemaDir}`);
}

const files = fs
  .readdirSync(schemaDir)
  .filter((entry) => entry.toLowerCase().endsWith('.json'))
  .sort((a, b) => a.localeCompare(b));

for (const file of files) {
  try {
    seedCollection(file);
  } catch (error) {
    print(`[mongo-seed] ${file}: skipped (${error.message})`);
  }
}

print(`[mongo-seed] Done for database "${dbName}"`);
