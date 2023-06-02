import React from 'react'
import { DocsThemeConfig } from 'nextra-theme-docs'

const config: DocsThemeConfig = {
  logo: <span>Larascord</span>,

  navigation: true,

  project: {
    link: 'https://github.com/JakyeRU/Larascord',
  },

  useNextSeoProps() {
    return {
      titleTemplate: '%s – Larascord',
    }
  },

  docsRepositoryBase: 'https://github.com/JakyeRU/Larascord/blob/main/docs',

  footer: {
    text: 'Larascord © 2023 by Jakye',
  },

}

export default config
