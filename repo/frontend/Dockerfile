# Stage 1: Install deps and build
FROM node:20-alpine AS builder

WORKDIR /app

COPY package.json package-lock.json* ./
RUN npm ci --frozen-lockfile

COPY . .
RUN npm run build

# Stage 2: Unit-test runner (node present, browser not required)
FROM builder AS test
# Inherits node, installed deps, and source from builder.
# Vitest runs in this stage via: docker compose run --rm frontend-test npx vitest run unit_tests/

# Stage 3: E2E test runner (Playwright chromium)
FROM builder AS e2e-runner
RUN npx playwright install --with-deps chromium

# Stage 4: Serve with nginx (production)
FROM nginx:alpine AS runtime

COPY --from=builder /app/dist /usr/share/nginx/html
COPY nginx.conf /etc/nginx/conf.d/default.conf

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]
