# Fluffy PO Robot

## Run the client

### View available commands:
```bash
docker run --rm --interactive --tty \
    --user 1000:1000 \
    --volume $PWD:/app \
    wingu/fluffy list
```

## Configuration

Run 
```bash
docker run --rm --interactive --tty \
    --user 1000:1000 \
    --volume $PWD:/app \
    wingu/fluffy init
```

to initialize the configuration file in an interactive mode.

Example configuration file (`poeditor.yaml`):
```yaml
api_token: XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
project_id: 123456
base_path: relative/path/to/translations
reference_language: en
languages:
    en: en
    de: de
    ro: ro
files:
    - source: validators.en.yaml
      context: validators
      translation: "%original_path%/validators.%language_code%.%file_extension%"
```
