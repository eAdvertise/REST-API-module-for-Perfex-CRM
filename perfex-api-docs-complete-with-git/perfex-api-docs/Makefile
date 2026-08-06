PYTHON ?= python3
VENV ?= .venv
VENV_PYTHON := $(VENV)/bin/python
VENV_PIP := $(VENV)/bin/pip
RUN_PYTHON := $(if $(wildcard $(VENV_PYTHON)),$(VENV_PYTHON),$(PYTHON))

ifneq (,$(wildcard .env))
include .env
export
endif

.PHONY: help install first-run bootstrap bootstrap-local update-upstream bootstrap-crm merge validate build qa serve clean package docker-build docker-up

help:
	@printf '%s\n' \
	  'make install          Create a virtual environment and install build dependencies' \
	  'make first-run        Install, import the locked upstream snapshot, and build' \
	  'make bootstrap        Import the locked public GitHub snapshot' \
	  'make bootstrap-local  Import an already downloaded upstream repository' \
	  'make update-upstream  Import the latest upstream main branch' \
	  'make bootstrap-crm    Import OpenAPI from EAD_CRM_BASE_URL using EAD_CRM_API_TOKEN' \
	  'make merge            Merge vendor and custom OpenAPI documents' \
	  'make validate         Run structural OpenAPI checks' \
	  'make build            Generate the static site/' \
	  'make qa               Check generated HTML links and assets' \
	  'make serve            Serve site/ at http://127.0.0.1:8080' \
	  'make package          Create a distributable ZIP' \
	  'make docker-up        Build and run the Nginx container'

install:
	$(PYTHON) -m venv $(VENV)
	$(VENV_PIP) install --upgrade pip
	$(VENV_PIP) install -r requirements.txt

first-run: install bootstrap build

bootstrap:
	$(RUN_PYTHON) scripts/bootstrap.py --source github

bootstrap-local:
	$(RUN_PYTHON) scripts/bootstrap.py --source local --local-path "$(UPSTREAM_LOCAL_PATH)"

update-upstream:
	$(RUN_PYTHON) scripts/bootstrap.py --source github --latest --force

bootstrap-crm:
	$(RUN_PYTHON) scripts/bootstrap.py --source crm --crm-url "$(EAD_CRM_BASE_URL)" --force

merge:
	$(RUN_PYTHON) scripts/merge_openapi.py

validate:
	$(RUN_PYTHON) scripts/validate_openapi.py openapi/combined.openapi.json

build: merge validate
	$(RUN_PYTHON) scripts/build_site.py

qa: build
	$(RUN_PYTHON) scripts/check_site.py site

serve: build
	$(RUN_PYTHON) -m http.server 8080 --directory site

clean:
	rm -rf site openapi/combined.openapi.json .cache

package: build
	bash scripts/package.sh

docker-build:
	docker build -t eAD-CRM-api-docs .

docker-up:
	docker compose up --build
