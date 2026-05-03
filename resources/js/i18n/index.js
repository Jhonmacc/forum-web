import { createI18n } from 'vue-i18n';

import ptBRCommon from './pt-BR/common.json';
import ptBRForum from './pt-BR/forum.json';
import ptBRComments from './pt-BR/comments.json';
import ptBRProfile from './pt-BR/profile.json';
import ptBRTags from './pt-BR/tags.json';
import ptBRDashboard from './pt-BR/dashboard.json';

import enCommon from './en/common.json';
import enForum from './en/forum.json';
import enComments from './en/comments.json';
import enProfile from './en/profile.json';
import enTags from './en/tags.json';
import enDashboard from './en/dashboard.json';

import ptBRPost from './pt-BR/post.json';
import enPost from './en/post.json';
import ptBRDocument from './pt-BR/document.json';
import enDocument from './en/document.json';
import ptBRAuth from './pt-BR/auth.json';
import enAuth from './en/auth.json';
import ptBRWelcome from './pt-BR/welcome.json';
import enWelcome from './en/welcome.json';

const messages = {
    'pt-BR': {
        common: ptBRCommon,
        forum: ptBRForum,
        comments: ptBRComments,
        profile: ptBRProfile,
        tags: ptBRTags,
        dashboard: ptBRDashboard,
        post: ptBRPost,
        document: ptBRDocument,
        auth: ptBRAuth,
        welcome: ptBRWelcome,
    },
    en: {
        common: enCommon,
        forum: enForum,
        comments: enComments,
        profile: enProfile,
        tags: enTags,
        dashboard: enDashboard,
        post: enPost,
        document: enDocument,
        auth: enAuth,
        welcome: enWelcome,
    },
};

const savedLocale = localStorage.getItem('locale') || 'pt-BR';

const i18n = createI18n({
    legacy: false,
    globalInjection: true,
    locale: savedLocale,
    fallbackLocale: 'pt-BR',
    messages,
});

export default i18n;
