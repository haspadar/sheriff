# syntax=docker/dockerfile:1

ARG DEBIAN_IMAGE=debian:trixie-slim
ARG NODE_MAJOR=24

ARG ACTIONLINT_VERSION=1.7.12
ARG HADOLINT_VERSION=2.14.0
ARG MARKDOWNLINT_VERSION=0.22.1
ARG TYPOS_VERSION=1.46.0
ARG SHELLCHECK_VERSION=0.11.0
ARG JSONLINT_VERSION=17.0.1
ARG SONAR_SCANNER_VERSION=8.0.1.6346

FROM ${DEBIAN_IMAGE}

ARG NODE_MAJOR
ARG ACTIONLINT_VERSION
ARG HADOLINT_VERSION
ARG MARKDOWNLINT_VERSION
ARG TYPOS_VERSION
ARG SHELLCHECK_VERSION
ARG JSONLINT_VERSION
ARG SONAR_SCANNER_VERSION

LABEL org.opencontainers.image.title="Sheriff Infra"
LABEL org.opencontainers.image.description="Infrastructure linters for Sheriff"
LABEL org.opencontainers.image.source="https://github.com/haspadar/sheriff"
LABEL org.opencontainers.image.licenses="MIT"

SHELL ["/bin/bash", "-o", "pipefail", "-c"]

# hadolint ignore=DL3008
RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends \
      ca-certificates \
      curl \
      git \
      bash \
      python3 \
      yamllint \
      xz-utils \
      gnupg; \
    rm -rf /var/lib/apt/lists/*; \
    \
    # --------------------------------------------------------\
    # Node.js (NodeSource) \
    # --------------------------------------------------------\
    mkdir -p /etc/apt/keyrings; \
    curl -fsSL https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key \
      | gpg --dearmor -o /etc/apt/keyrings/nodesource.gpg; \
    echo "deb [signed-by=/etc/apt/keyrings/nodesource.gpg] https://deb.nodesource.com/node_${NODE_MAJOR}.x nodistro main" \
      > /etc/apt/sources.list.d/nodesource.list; \
    apt-get update; \
    apt-get install -y --no-install-recommends nodejs; \
    rm -rf /var/lib/apt/lists/*; \
    \
    # --------------------------------------------------------\
    # Architecture detection \
    # --------------------------------------------------------\
    ARCH="$(uname -m)"; \
    case "$ARCH" in \
      x86_64) \
        ACTIONLINT_ARCH="amd64"; \
        HADOLINT_ARCH="x86_64"; \
        TYPOS_ARCH="x86_64"; \
        SHELLCHECK_ARCH="x86_64"; \
        ;; \
      aarch64) \
        ACTIONLINT_ARCH="arm64"; \
        HADOLINT_ARCH="arm64"; \
        TYPOS_ARCH="aarch64"; \
        SHELLCHECK_ARCH="aarch64"; \
        ;; \
      *) echo "Unsupported architecture: $ARCH"; exit 1 ;; \
    esac; \
    \
    # --------------------------------------------------------\
    # Infra linters \
    # --------------------------------------------------------\
    mkdir -p /tmp/actionlint; \
    curl -sSfL \
      "https://github.com/rhysd/actionlint/releases/download/v${ACTIONLINT_VERSION}/actionlint_${ACTIONLINT_VERSION}_linux_${ACTIONLINT_ARCH}.tar.gz" \
      | tar -xz -C /tmp/actionlint; \
    mv /tmp/actionlint/actionlint /usr/local/bin/actionlint; \
    chmod +x /usr/local/bin/actionlint; \
    rm -rf /tmp/actionlint; \
    \
    curl -sSfL \
      "https://github.com/hadolint/hadolint/releases/download/v${HADOLINT_VERSION}/hadolint-linux-${HADOLINT_ARCH}" \
      -o /usr/local/bin/hadolint; \
    chmod +x /usr/local/bin/hadolint; \
    \
    mkdir -p /tmp/typos; \
    curl -sSfL \
      "https://github.com/crate-ci/typos/releases/download/v${TYPOS_VERSION}/typos-v${TYPOS_VERSION}-${TYPOS_ARCH}-unknown-linux-musl.tar.gz" \
      | tar -xz -C /tmp/typos; \
    mv /tmp/typos/typos /usr/local/bin/typos; \
    chmod +x /usr/local/bin/typos; \
    rm -rf /tmp/typos; \
    \
    curl -sSfL \
      "https://github.com/koalaman/shellcheck/releases/download/v${SHELLCHECK_VERSION}/shellcheck-v${SHELLCHECK_VERSION}.linux.${SHELLCHECK_ARCH}.tar.xz" \
      | tar -xJ; \
    mv "shellcheck-v${SHELLCHECK_VERSION}/shellcheck" /usr/local/bin/shellcheck; \
    chmod +x /usr/local/bin/shellcheck; \
    rm -rf "shellcheck-v${SHELLCHECK_VERSION}"; \
    \
    npm install -g \
      "markdownlint-cli2@${MARKDOWNLINT_VERSION}" \
      "@prantlf/jsonlint@${JSONLINT_VERSION}"; \
    npm cache clean --force; \
    \
    # --------------------------------------------------------\
    # SonarScanner CLI \
    # --------------------------------------------------------\
    SONAR_ARCH="$(uname -m | sed 's/x86_64/linux-x64/' | sed 's/aarch64/linux-aarch64/')"; \
    curl -sSfL \
      "https://binaries.sonarsource.com/Distribution/sonar-scanner-cli/sonar-scanner-cli-${SONAR_SCANNER_VERSION}-${SONAR_ARCH}.zip" \
      -o /tmp/sonar-scanner.zip; \
    apt-get update; \
    apt-get install -y --no-install-recommends unzip; \
    unzip -q /tmp/sonar-scanner.zip -d /opt; \
    ln -s "/opt/sonar-scanner-${SONAR_SCANNER_VERSION}-${SONAR_ARCH}/bin/sonar-scanner" /usr/local/bin/sonar-scanner; \
    rm /tmp/sonar-scanner.zip; \
    apt-get purge -y unzip; \
    apt-get autoremove -y; \
    rm -rf /var/lib/apt/lists/*; \
    \
    # --------------------------------------------------------\
    # Non-root runtime \
    # --------------------------------------------------------\
    useradd -m -u 10001 sheriff; \
    mkdir -p /project; \
    chown -R sheriff:sheriff /project

USER sheriff
WORKDIR /project
